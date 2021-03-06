<?php
// 
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
// 
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
// 
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.     
// 
// @Author Karthik Tharavaad 
//         karthik_tharavaad@yahoo.com
// @Contributor Maurice Svay
//              maurice@svay.Com
//
// codediesel.com 6th July 2011
// Added new method : cropFace()


class FaceDetector {
    
    protected static $detection_data;
    protected static $canvas;
    protected static $face;
    private static $reduced_canvas;
    
    public static function construct($detection_file = 'detection.dat') {
        if (is_file($detection_file)) {
            FaceDetector::$detection_data = unserialize(file_get_contents($detection_file));
        } else {
            throw new Exception("Couldn't load detection data");
        }
    }
    
    public static function face_detect($file) {
        if (!is_file($file)) {
            throw new Exception("Can not load $file");
        }
        
        FaceDetector::$canvas = imagecreatefromjpeg($file);
        $im_width = imagesx(FaceDetector::$canvas);
        $im_height = imagesy(FaceDetector::$canvas);

        //Resample before detection?
        $ratio = 0;
        $diff_width = 320 - $im_width;
        $diff_height = 240 - $im_height;
        if ($diff_width > $diff_height) {
            $ratio = $im_width / 320;
        } else {
            $ratio = $im_height / 240;
        }

        if ($ratio != 0) {
            FaceDetector::$reduced_canvas = imagecreatetruecolor($im_width / $ratio, $im_height / $ratio);
            imagecopyresampled(FaceDetector::$reduced_canvas, FaceDetector::$canvas, 0, 0, 0, 0, $im_width / $ratio, $im_height / $ratio, $im_width, $im_height);
            
            $stats = FaceDetector::get_img_stats(FaceDetector::$reduced_canvas);
            FaceDetector::$face = FaceDetector::$do_detect_greedy_big_to_small($stats['ii'], $stats['ii2'], $stats['width'], $stats['height']);
            FaceDetector::$face['x'] *= $ratio;
            FaceDetector::$face['y'] *= $ratio;
            FaceDetector::$face['w'] *= $ratio;
        } else {
            $stats = FaceDetector::get_img_stats(FaceDetector::$canvas);
            FaceDetector::$face = FaceDetector::$do_detect_greedy_big_to_small($stats['ii'], $stats['ii2'], $stats['width'], $stats['height']);
        }
        return (FaceDetector::$face['w'] > 0);
    }
      public static function getX() {
	  return FaceDetector::$face['x'];
	  }
	  public static function getY() {
	  return FaceDetector::$face['y'];
	  }
	  public static function getW() {
	  return FaceDetector::$face['w'];
	  }
    
    public static function toJpeg() {
        $color = imagecolorallocate(FaceDetector::$canvas, 255, 0, 0); //red
        imagerectangle(FaceDetector::$canvas, FaceDetector::$face['x'], FaceDetector::$face['y'], FaceDetector::$face['x']+FaceDetector::$face['w'], FaceDetector::$face['y']+ FaceDetector::$face['w'], $color);
        header('Content-type: image/jpeg');
        imagejpeg(FaceDetector::$canvas);
    }
    
    /* Added by codediesel */
    public static function cropFace() {
        $canvas = imagecreatetruecolor(FaceDetector::$face['w'], FaceDetector::$face['w']);
		echo FaceDetector::$face['x'];
        imagecopy($canvas, FaceDetector::$canvas, 0, 0, FaceDetector::$face['x'], FaceDetector::$face['x'], FaceDetector::$face['w'], FaceDetector::$face['w']);
       // header('Content-type: image/jpeg');
        //imagejpeg($canvas);
    }
    
    public static function toJson() {
        return "{'x':" . FaceDetector::$face['x'] . ", 'y':" . FaceDetector::$face['y'] . ", 'w':" . FaceDetector::$face['w'] . "}";
    }
    
    public static function getFace() {
        return FaceDetector::$face;
    }
    
    protected static function get_img_stats($canvas){
        $image_width = imagesx($canvas);
        $image_height = imagesy($canvas);     
        $iis =  FaceDetector::compute_ii($canvas, $image_width, $image_height);
        return array(
            'width' => $image_width,
            'height' => $image_height,
            'ii' => $iis['ii'],
            'ii2' => $iis['ii2']
        );         
    }
    
    protected static function compute_ii($canvas, $image_width, $image_height ){
        $ii_w = $image_width+1;
        $ii_h = $image_height+1;
        $ii = array();
        $ii2 = array();      
                                
        for($i=0; $i<$ii_w; $i++ ){
            $ii[$i] = 0;
            $ii2[$i] = 0;
        }                        
                                    
        for($i=1; $i<$ii_w; $i++ ){  
            $ii[$i*$ii_w] = 0;       
            $ii2[$i*$ii_w] = 0; 
            $rowsum = 0;
            $rowsum2 = 0;
            for($j=1; $j<$ii_h; $j++ ){
                var_dump($canvas); 
                $rgb = ImageColorAt($canvas, $j, $i);
                $red = ($rgb >> 16) & 0xFF;
                $green = ($rgb >> 8) & 0xFF;
                $blue = $rgb & 0xFF;
                $grey = ( 0.2989*$red + 0.587*$green + 0.114*$blue )>>0;  // this is what matlab uses
                $rowsum += $grey;
                $rowsum2 += $grey*$grey;
                
                $ii_above = ($i-1)*$ii_w + $j;
                $ii_this = $i*$ii_w + $j;
                
                $ii[$ii_this] = $ii[$ii_above] + $rowsum;
                $ii2[$ii_this] = $ii2[$ii_above] + $rowsum2;
            }
        }
        return array('ii'=>$ii, 'ii2' => $ii2);
    }
    
    protected static function do_detect_greedy_big_to_small( $ii, $ii2, $width, $height ){
        $s_w = $width/20.0;
        $s_h = $height/20.0;
        $start_scale = $s_h < $s_w ? $s_h : $s_w;
        $scale_update = 1 / 1.2;
        for($scale = $start_scale; $scale > 1; $scale *= $scale_update ){
            $w = (20*$scale) >> 0;
            $endx = $width - $w - 1;
            $endy = $height - $w - 1;
            $step = max( $scale, 2 ) >> 0;
            $inv_area = 1 / ($w*$w);
            for($y = 0; $y < $endy ; $y += $step ){
                for($x = 0; $x < $endx ; $x += $step ){
                    $passed = FaceDetector::$detect_on_sub_image( $x, $y, $scale, $ii, $ii2, $w, $width+1, $inv_area);
                    if( $passed ) {
                        return array('x'=>$x, 'y'=>$y, 'w'=>$w);
                    }
                } // end x
            } // end y
        }  // end scale
        return null;
    }
    
    protected static function detect_on_sub_image( $x, $y, $scale, $ii, $ii2, $w, $iiw, $inv_area){
        $mean = ( $ii[($y+$w)*$iiw + $x + $w] + $ii[$y*$iiw+$x] - $ii[($y+$w)*$iiw+$x] - $ii[$y*$iiw+$x+$w]  )*$inv_area;
        $vnorm =  ( $ii2[($y+$w)*$iiw + $x + $w] + $ii2[$y*$iiw+$x] - $ii2[($y+$w)*$iiw+$x] - $ii2[$y*$iiw+$x+$w]  )*$inv_area - ($mean*$mean);    
        $vnorm = $vnorm > 1 ? sqrt($vnorm) : 1;
        
        $passed = true;
        for($i_stage = 0; $i_stage < count(FaceDetector::$detection_data); $i_stage++ ){
            $stage = FaceDetector::$detection_data[$i_stage];  
            $trees = $stage[0];  

            $stage_thresh = $stage[1];
            $stage_sum = 0;
                              
            for($i_tree = 0; $i_tree < count($trees); $i_tree++ ){
                $tree = $trees[$i_tree];
                $current_node = $tree[0];    
                $tree_sum = 0;
                while( $current_node != null ){
                    $vals = $current_node[0];
                    $node_thresh = $vals[0];
                    $leftval = $vals[1];
                    $rightval = $vals[2];
                    $leftidx = $vals[3];
                    $rightidx = $vals[4];
                    $rects = $current_node[1];
                    
                    $rect_sum = 0;
                    for( $i_rect = 0; $i_rect < count($rects); $i_rect++ ){
                        $s = $scale;
                        $rect = $rects[$i_rect];
                        $rx = ($rect[0]*$s+$x)>>0;
                        $ry = ($rect[1]*$s+$y)>>0;
                        $rw = ($rect[2]*$s)>>0;  
                        $rh = ($rect[3]*$s)>>0;
                        $wt = $rect[4];
                        
                        $r_sum = ( $ii[($ry+$rh)*$iiw + $rx + $rw] + $ii[$ry*$iiw+$rx] - $ii[($ry+$rh)*$iiw+$rx] - $ii[$ry*$iiw+$rx+$rw] )*$wt;
                        $rect_sum += $r_sum;
                    } 
                     
                    $rect_sum *= $inv_area;
                         
                    $current_node = null;
                    if( $rect_sum >= $node_thresh*$vnorm ){
                        if( $rightidx == -1 ) 
                            $tree_sum = $rightval;
                        else
                            $current_node = $tree[$rightidx];
                    } else {
                        if( $leftidx == -1 )
                            $tree_sum = $leftval;
                        else
                            $current_node = $tree[$leftidx];
                    }
                } 
                $stage_sum += $tree_sum;
            } 
            if( $stage_sum < $stage_thresh ){
                return false;
            }
        } 
        return true;
    }
}

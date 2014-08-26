$(function() {
    $("#fileupload").on("click", ".arreglar", function() {
        $("#modalArreglar").modal();
    });

    $('#target').Jcrop({
        aspectRatio: 1,
        minSize: 50,
        onSelect: applyCoords,
        onChange: applyCoords
    });
});
 function applyCoords(c){
       console.log("X : " + c.x + ", Y : " + c.y + ", X2 : " + c.x2+ ", Y2 : " + c.y2+ ", W : " + c.w+ ", H : " + c.h)
      }

$(function() {


    $("#fileupload").on("click", ".arreglar", function() {
        var original = $(this).data("original");
        $("#target").attr("src", original);
        $('.jcrop-holder img').attr('src', original);
        $('#target').Jcrop({
            aspectRatio: 1,
            minSize: 50,
            onSelect: applyCoords,
            onChange: applyCoords
        });
        $("#modalArreglar").modal();

    });


});
function applyCoords(c) {
    console.log("X : " + c.x + ", Y : " + c.y + ", X2 : " + c.x2 + ", Y2 : " + c.y2 + ", W : " + c.w + ", H : " + c.h)
}

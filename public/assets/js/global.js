$(function() {


    $("#fileupload").on("click", ".arreglar", function() {
        $("#botonFix").prop("disabled", false);
        $("#nombreImagen").val($(this).data("nombre"));

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
        $(this).parent().parent().find("img").addClass("recargarImg");

    });

    $("#botonFix").click(function() {
        var x = $("#x").val();
        var y = $("#y").val();
        var w = $("#w").val();
        var nombreImagen = $("#nombreImagen").val();

        if (x != "" && nombreImagen != "") {
            $(this).prop("disabled", true);
            $.ajax({
                url: "/fixImage",
                method: "post",
                data: {
                    x: x,
                    y: y,
                    w: w,
                    nombreImagen: nombreImagen,
                },
                success: function(data) {
                    $("#modalArreglar").modal("hide");
                    d = new Date();
                    $(".recargarImg").each(function(index) {
                        var nuevaUrl = $(this).attr("src") + "?" + d.getTime();
                        $(this).attr("src", nuevaUrl);
                        $(this).parent().attr("href", nuevaUrl)
                    });
                }
            });
        }

    });

});
function applyCoords(c) {
    $("#x").val(c.x);
    $("#y").val(c.y);
    $("#w").val(c.w);

}

$(function () {
  $("select").material_select();

  var rango = $("#rangoPrecio");
  var limites = rango.val().split(";").map(Number);
  rango.ionRangeSlider({
    type: "double",
    min: 0,
    max: Number(rango.attr("data-max")),
    from: limites[0],
    to: limites[1],
    prefix: "$"
  });
});

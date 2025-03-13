$(document).ready(function () {
  var additionalFeatureLimit = additionalFeatureLimit;
});
var addItem = function (key) {
  rpItemNode = '';
  let it = $(".js-repeater-item:last-child").index() + 1;


  rpItemNode += `<div class="js-repeater-item p-3 pb-0" data-item="${it}">
                        <div class="row align-items-end gutters-2">`
  for (var Itemkey in languages) {
    rpItemNode += `<div class="col-sm-4 col-lg-3">
                            <label for="form" class="form-label mb-1 ${languages[Itemkey].direction == 1 ? 'rtl text-right' : ''}" ">Label (In ${languages[Itemkey].name})</label>
                            <div class="mb-2">
                                <input type="text" required class="form-control ${languages[Itemkey].direction == 1 ? 'rtl text-right' : ''}"
                                 placeholder="" name="${languages[Itemkey].code}_feature_heading[]">
                            </div>
                            </div>`
  }
  rpItemNode += `<button class="btn btn-danger btn-sm js-repeater-remove mb-2 mr-2" type="button"
                                        onclick="$(this).parents('.js-repeater-item').remove()">X</button>
                                <button class="btn btn-success btn-sm js-repeater-child-add mb-2" type="button" data-it="${it}">Add Option</button>
                            <div class="repeater-child-list col-12" id="options${it}"></div>
                        </div>
                    </div>`;
  $("#js-repeater-container").append(rpItemNode);
};
/* find elements */
var repeater = $(".js-repeater");
var key = 0;
var addBtn = repeater.find('.js-repeater-add');
var items = $(".js-repeater-item");
var it = $(".js-repeater-item").index();

if (key <= 0) {
  /* handle click and add items */
  addBtn.on("click", function () {
    if ($("#js-repeater-container .js-repeater-item").length <= additionalFeatureLimit) {
      key++;
      addItem(key);
    } else {
      bootnotify('Maximum number of features reached.!', 'Alert', 'warning');
    }
  });
}

$(document).on('click', '.js-repeater-child-add', function () {
  option = ''
  let it = $(this).data('it');
  let cit = $(this).parent().find(".repeater-child-item:last-child").index();

  console.log('cit', cit);
  console.log('it', it);
  let parent = $(this).parent().find("#options" + it);

  option += `<div class="repeater-child-item mb-3" id="options${it + '' + cit}">
                <div class="row align-items-end gutters-2">`
  for (var optionkey in languages) {
    option += `<div class="col-sm-4 col-lg-3 mb-2">
                        <label for="form" class="form-label mb-1">Value (In ${languages[optionkey].name})</label>
                        <input required name="${languages[optionkey].code}_feature_value_${it}[]" type="text" class="form-control ${languages[optionkey].direction == 1 ? 'rtl text-right' : ''}"
                            placeholder="">
                    </div>`
  }
  option += `<div class="col-sm-4 col-lg-3 mb-2">
                        <button class="btn btn-danger js-repeater-child-remove btn-sm" type="button"
                            onclick="$(this).parents('.repeater-child-item').remove()">X</button>
                    </div>
                    </div>
                </div>`;
  $(parent).append(option);
})

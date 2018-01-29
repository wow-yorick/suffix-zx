//$('#selectAll').on('click',function () {
function selectAllItem(obj) {
    var _thisBtn = $(obj);
    _thisBtn.toggleClass('selected');
    if(_thisBtn.hasClass('selected')) {
        $('label.option').addClass('selected');
    } else {
        $('label.option').removeClass('selected');
    }
    _monitorSelect();
}
//});
//产品详情
$('.header-nav').find('a').on('click',function () {
    $('.header-nav').find('a').removeClass('on');
    $(this).addClass('on');
})

//减少
function decrease(obj) {
    var thisBtn = $(obj);
    if(thisBtn.hasClass('disabled')) {
        return false;
    }
    var actTag = $('#number');
    if(actTag.val() <= 1) {
        actTag.val(1);
        thisBtn.addClass('disabled');
        return false;
    }
    actTag.val(parseInt(actTag.val()) - 1);
}
//增加
function increase(obj) {
    var thisBtn = $(obj);
    var actTag = $('#number');
    if(actTag.val() >= 1) {
        $('.decrease').removeClass('disabled');
    }
    actTag.val(parseInt(actTag.val()) + 1);
}

//产品详情 end

function toggleSelectOrder(obj) {
    var _thisBtn = $(obj);
    _thisBtn.toggleClass('selected');
    _monitorSelect();
}
function _monitorSelect() {
    var product_total = 0;
    var price_total = 0;
    var itemTotal = 0;
    $('.cart-commodity').each(function() {
        var objCur = $(this);
        if(objCur.find('label.option').hasClass('selected')) {
            var curQuantity = objCur.find('.quantity').text();
            var curTotalPrice= objCur.find('.totalPrice').text();
            price_total += parseInt(curTotalPrice);
            product_total += 1;
            itemTotal += parseInt(curQuantity);
        }
    });
    $('.price_total').text(price_total);
    $('.product_total').text(product_total);
    $('.itemTotal').text(itemTotal);
}

//购物车编辑
function cartEditBtn(obj) {
    var _thisBtn = $(obj);
    _thisBtn.hide();
    $('#cartEditAffirmBtn').show();

    $('.showPrice').hide();
    $('.editQuantity').show();

    $('.pay-tatol').hide();
    $('#cartClearBtn').hide();
    $('#cartDeleteBtn').show();
}
//购物车编辑确定
function cartEditAffirmBtn(obj) {
    var _thisBtn = $(obj);
    _thisBtn.hide();
    $('#cartEditBtn').show();

    $('.showPrice').show();
    $('.editQuantity').hide();

    $('.pay-tatol').show();
    $('#cartClearBtn').show();
    $('#cartDeleteBtn').hide();
    var updateInput = '<input type="hidden" name="op" value="Update cart" />';
    $('#cartEditForm').append(updateInput);
    $('#cartEditForm').submit();
}

//数量减少
$('.decrease').on('click',function () {
    var _thisBtn = $(this);
    var numObj = _thisBtn.parent().find('.number');
    var tagNum = parseInt(numObj.val()) - 1;
    tagNum = tagNum <= 0 ? 0 :tagNum;
    numObj.val(tagNum);
    var parentEle = _thisBtn.parents('.wares-info');
    var quaObj = parentEle.find('.quantity');
    var singlePrice = parseInt(quaObj.attr('data-single-price'));
    quaObj.text(numObj.val());
    parentEle.find('.totalPrice').text(singlePrice*numObj.val());
    _monitorSelect();
});

//数量增加
$('.increase').on('click',function () {
    var _thisBtn = $(this);
    var numObj = _thisBtn.parent().find('.number');
    numObj.val(parseInt(numObj.val()) + 1);
    var parentEle = _thisBtn.parents('.wares-info');
    var quaObj = parentEle.find('.quantity');
    var singlePrice = parseInt(quaObj.attr('data-single-price'));
    quaObj.text(numObj.val());
    parentEle.find('.totalPrice').text(singlePrice*numObj.val());
    _monitorSelect();
});

//购物车删除条目
function cartDeleteBtn(obj) {
    $('.cart-commodity').each(function() {
        var objCur = $(this);
        if(objCur.find('label.option').hasClass('selected')) {
            var name = 'delete-order-item-'+objCur.attr('data-order-key');
            var removeHiddenInput = '<input type="hidden" name="'+name+'" value="Remove" />';
            $('#cartEditForm').append(removeHiddenInput);
            //objCur.remove();
        }
    });
    //$('#cartEditAffirmBtn').click();
    //$('#selectAll').click();
    $('#cartEditForm').submit();
}

function cartClearBtn(obj) {
    var itemNum = $(obj).find('.itemTotal').text();
    if(itemNum <=0) {
        alert("请勾选产品进行结算!")
        return false;
    }

    $('.cart-commodity').each(function() {
        var objCur = $(this);
        $('#jesuan-text').text('提交中...');
        if(!objCur.find('label.option').hasClass('selected')) {
            var name = 'delete-order-item-'+objCur.attr('data-order-key');
            var removeHiddenInput = '<input type="hidden" name="'+name+'" value="Remove" />';
            $('#cartEditForm').append(removeHiddenInput);
            //objCur.remove();
        }
        var checkoutInput = '<input type="hidden" name="op" value="Checkout" />';
        $('#cartEditForm').append(checkoutInput);
        //alert($('#cartEditForm').serialize());
        //return false;

        $.post('/cart',$('#cartEditForm').serialize(), function() {
        //  location.href="/cart/confirm";
            $('#cartEditForm').submit();
        });
    });
}

//home
var swiper = new Swiper('.home-swiper', {
    pagination: {
        el: '.swiper-pagination',
    },
});

//结帐
function goToCheckout(obj) {
    var beizhu = $('[name=field_liuyanbeizhu]').val();
    if(!beizhu) {
        alert("请填写留言备注!");
        return false;
    }

    var checkoutInput = '<input type="hidden" name="op" value="Continue to review" />';
    $('#commerce-checkout-flow-multistep-default').append(checkoutInput);
    $('#commerce-checkout-flow-multistep-default').submit();
}

//订单tab切换
$('.nav-tab-top ul.flex').find('li').on('click',function () {
    var allTabObj = $('.nav-tab-top ul.flex').find('li');
    allTabObj.removeClass('cur');
    $(this).addClass('cur');
    $('.scroll-wrap').hide();
    var currentArea = $(this).attr('data-code');
    $('div.scroll-wrap[data-index='+currentArea+']').show();
});

//地址修改
function saveAddressEdit() {
    var profileForm = $('#profile-customer-edit-form,#profile-customer-add-form');
    var isDefault = $('#hasSetDefault').hasClass('switchery-open');
    if(profileForm.find('[name=op]').length == 0) {
        var submitInput = '<input type="hidden" name="op" value="保存" />';
        profileForm.append(submitInput);
    }
    //alert(profileForm.find('[name=op]').length);
    var opVal = '保存';
    if(isDefault) {
        opVal = 'Save and make default';
    }
    profileForm.find('[name=op]').val(opVal);
    //alert(profileForm.find('[name=op]').val());
    _getGivenAndfamilyName();
    profileForm.submit();
}

function _getGivenAndfamilyName() {
  var username = $('[name=username]').val();
  if(!username || username.length < 2) {
      return false;
  }
  $('#given_name').val(username.substring(0,1));
  $('#family_name').val(username.substring(1,username.length));

}

//toggle address is default
$('#hasSetDefault').on('click', function () {
    $(this).toggleClass('switchery-open');
})
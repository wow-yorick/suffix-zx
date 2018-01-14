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
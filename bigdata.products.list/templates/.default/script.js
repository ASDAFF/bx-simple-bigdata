function getCookie(name) {
    var matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}

function rememberRecommend(rcmId, productId){

    var plCookieName = bigData.cookie_prefix + '_RCM_PRODUCT_LOG';
    var plCookie = getCookie(plCookieName);
    var itemFound = false;
    var current_server_time = new Date().getTime();

    var cItems = [],
        cItem;

    if (plCookie)
    {
        cItems = plCookie.split('.');
    }

    var i = cItems.length;

    while (i--)
    {
        cItem = cItems[i].split('-');

        if (cItem[0] == productId)
        {
            cItem = cItems[i].split('-');

            cItem[1] = rcmId;
            cItem[2] = current_server_time;

            cItems[i] = cItem.join('-');
            itemFound = true;
        }
        else
        {
            if ((current_server_time - cItem[2]) > 3600*24*30)
            {
                cItems.splice(i, 1);
            }
        }
    }

    if (!itemFound)
    {
        cItems.push([productId, rcmId, current_server_time].join('-'));
    }
    var plNewCookie = cItems.join('.');

    var cookieDate = new Date(new Date().getTime() + 1000*3600*24*365*10);
    document.cookie=plCookieName+"="+plNewCookie+"; path=/; expires="+cookieDate.toUTCString()+"; domain="+bigData.cookie_domain;
}

function getProducts(ids){
    console.log('Getting ajax page...');
    $.ajax({
        url : bigData.ajaxElementsPage,
        data: {'IDS': ids}
    }).done(function(html) {
        console.log('Ajax page is getted');
        if (!html)
            console.log('No items found');
        $('.bigdata-recommended .bigdata-wrap').html(html);
    });
}

$(document).ready(function(){

    //Сопроводить все ссылки на детальную кароточку классом "bx_rcm_view_link" и атрибутом data-product-id в котором лежит значение ID элемента
    $(document).on('click', ".bigdata-recommended ." + bigData.detailPageUrlRecommendedClass, function(event){
        event.preventDefault();

        var _this = $(this);
        var productId = _this.data('product-id');

        rememberRecommend(bigData.recommendationId, productId);

        location.href = _this.attr('href');
    });

    //класс с добавлением в корзину (сопроводить атрибутом data-product-id)
    $(document).on('click',".bigdata-recommended ." + bigData.addaddToCartActionClass, function(e){
        e.preventDefault();

        var _this = $(this);
        var productId = _this.data('product-id');

        rememberRecommend(bigData.recommendationId, productId);
    });

    console.log('Getting ids...');

    $.ajax({
        method : "POST",
        url : bigData.url,
        data: bigData.data
    }).done(function(response) {
        response = JSON.parse(response);
        if (!response.items)
            return;

        console.log('Ids is getted');

        getProducts(response.items);
    });

});
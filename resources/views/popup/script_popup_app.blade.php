<script>
    function ajax_settings(url, method, data){
        return {
            "url": url,
            "method": method,
            "timeout": 0,
            "headers": {"Content-Type": "application/json",},
            "data": data,
        }
    }


    function payment_type_on_set_option(type, price){
        window.document.getElementById('valueSelector').value = type

        let Cash = window.document.getElementById('Visibility_Cash')
        let Card = window.document.getElementById('Visibility_Card')

        let input_cash = window.document.getElementById('cash')
        let input_card = window.document.getElementById('card')

        input_cash.value = ''
        input_card.value = ''
        Cash.style.display = 'none'
        Card.style.display = 'none'

        switch (type) {
            case 1 && "1": {
                Cash.style.display = 'block'
                input_cash.value = price
                break
            }
            case 2 && "2": {
                Card.style.display = 'block'
                input_card.value = price
                input_card.disabled = true
                break
            }
            default: {

            }

        }
    }


    function PrintCheck(){
        let url = 'https://dev.smarttis.kz/Popup/customerorder/print';
        let final = url + '/' + accountId;
        window.open(final)
    }


    function SelectorSum(value){
        window.document.getElementById("cash").value = ''
        window.document.getElementById("card").value = ''

        if (value === "1") {
            document.getElementById('Visibility_Cash').style.display = 'block';
            document.getElementById('Visibility_Card').style.display = 'none';
        }
        if (value === "2") {
            document.getElementById('Visibility_Card').style.display = 'block';
            document.getElementById('Visibility_Cash').style.display = 'none';
            let card =  window.document.getElementById("card");
            card.value = window.document.getElementById("sum").innerText
            window.document.getElementById("card").disabled = true
        }
        if (value === "3") {
            document.getElementById('Visibility_Cash').style.display = 'block';
            document.getElementById('Visibility_Card').style.display = 'block';
            //document.getElementById('Visibility_Mobile').style.display = 'block';
            window.document.getElementById("card").disabled = false
        }

    }
    function option_value_error_fu(index_option, money_card, money_cash){
        let params = false
        switch (index_option) {
            case 1 && "1": {
                if (!money_cash) {
                    window.document.getElementById('messageAlert').innerText = 'Вы не ввели сумму наличных'
                    window.document.getElementById('message').style.display = "block"
                    params = true
                }
                break
            }
            case 2 && "2": {
                if (!money_cash) {
                    window.document.getElementById('messageAlert').innerText = 'Вы не ввели сумму карты'
                    window.document.getElementById('message').style.display = "block"
                    params = true
                }
                break
            }
            case 3 && "3": {
                if (!money_card && !money_cash){
                    window.document.getElementById('messageAlert').innerText = 'Вы не ввели сумму'
                    window.document.getElementById('message').style.display = "block"
                    params = true
                }
                break
            }
            default: {

            }

        }
        return params
    }
    function updateQuantity(id, params){
        let object_Quantity = window.document.getElementById('productQuantity_'+id);
        let Quantity = parseInt(object_Quantity.innerText)

        if (Quantity >= 0 ){

            let object_price = window.document.getElementById('productPrice_'+id).innerText;
            let object_Final = window.document.getElementById('productFinal_'+id);

            let object_sum = window.document.getElementById('sum');
            let sum = parseFloat(object_sum.innerText - object_Final.innerText)

            if (params === 'plus'){
                object_Quantity.innerText = Quantity + 1
                object_Final.innerText = object_Quantity.innerText * object_price
                object_sum.innerText = parseFloat(sum + parseFloat(object_Final.innerText))
            }
            if (params === 'minus'){
                object_Quantity.innerText = Quantity - 1
                object_Final.innerText = object_Quantity.innerText * object_price
                object_sum.innerText = parseFloat(sum + parseFloat(object_Final.innerText))
                if (parseInt(object_Quantity.innerText) === 0){
                    deleteBTNClick( id )
                }
            }
        } else deleteBTNClick( id )

    }


    function newPopup(){
        window.document.getElementById("sum").innerHTML = ''

        window.document.getElementById("message").style.display = "none"
        window.document.getElementById("messageGood").style.display = "none"

        window.document.getElementById("refundCheck").style.display = "none"
        window.document.getElementById("getKKM").style.display = "none"
        window.document.getElementById("ShowCheck").style.display = "none"

        window.document.getElementById("cash").value = ''
        window.document.getElementById("card").value = ''

        window.document.getElementById("cash").style.display = "block"
        window.document.getElementById("valueSelector").value = 1
        SelectorSum(0)
    }


    function roundToTwo(num) {
        return +(Math.round(num + "e+2")  + "e-2");
    }
    function isNumberKeyCash(evt){
        let charCode = (evt.which) ? evt.which : event.keyCode
        if (charCode === 46){
            let inputValue = $("#cash").val();
            let count = (inputValue.match(/'.'/g) || []).length;
            if(count<1){
                return inputValue.indexOf('.') < 1;

            }else{
                return false;
            }
        }
        return !(charCode !== 46 && charCode > 31 && (charCode < 48 || charCode > 57));

    }
    function isNumberKeyCard(evt){
        let charCode = (evt.which) ? evt.which : event.keyCode
        if (charCode === 46){
            let inputValue = $("#card").val();
            let count = (inputValue.match(/'.'/g) || []).length;
            if(count<1){
                return inputValue.indexOf('.') < 1;

            }else{
                return false;
            }
        }
        return !(charCode !== 46 && charCode > 31 && (charCode < 48 || charCode > 57));

    }


    function deleteBTNClick(Object){

        let sum = document.getElementById("sum").innerHTML;
        let final = document.getElementById('productFinal_' + Object).innerHTML;
        window.document.getElementById("sum").innerHTML = sum-final;


        window.document.getElementById('productName_' + Object).innerHTML = '';
        window.document.getElementById('productQuantity_' + Object).innerHTML = '';
        window.document.getElementById('productPrice_' + Object).innerHTML = '';
        window.document.getElementById('productVat_' + Object).innerHTML = '';
        window.document.getElementById('productDiscount_' + Object).innerHTML = '';
        window.document.getElementById('productFinal_' + Object).innerHTML = '';
        window.document.getElementById(Object).style.display = "none";
    }



    //Дополнительный
    function formatParams(params) {
        return "?" + Object
            .keys(params)
            .map(function (key) {
                return key + "=" + encodeURIComponent(params[key])
            })
            .join("&")
    }
</script>

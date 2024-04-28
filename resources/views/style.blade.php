<style>

    body {
        font-family: 'Helvetica', 'Arial', sans-serif;
        color: #444444;
        background-color:#dcdcdc;
        height: 800px;
        max-height: 2280px;


        font-size: 0.9rem;
        min-font-size: 14px;
    }


    .gradient{
        background-image: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    }
    .gradient_invert{
        background-image: linear-gradient(135deg, #c3cfe2 0%, #f5f7fa 100%);
    }



    .main-container {
        display: flex;
        flex-direction: column;
        height: 100vh;
    }
    .content-container {
        overflow-y: auto;
        overflow-x: hidden;
        flex-grow: 1;
    }

    .gradient_focus:hover{
        color: white;
        border: 0;
        background-image: linear-gradient(147deg, #ffb13b 0%, #FF2525 74%);
    }

    .gradient_focus:active{
        background-image: linear-gradient(147deg, #ffb13b 0%, #FF2525 74%);
        border: 0;
        background-size: 100%;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        -moz-text-fill-color: transparent;
    }

    .gradient_focus:focus{
        background-color: #fff;
        border-color: #dbdbdb;
        border-width: 1px;
        box-shadow: 0 0 0 0 !important;
    }

    .gradient_a a.is-active {
        background-image: linear-gradient(147deg, #ffb13b 0%, #FF2525 74%);
        background-clip: text !important;
        -webkit-background-clip: text !important;
        color: transparent !important;
        border-bottom-color: #000000;
    }

    .gradient_a a:hover{
        background-image: linear-gradient(147deg, #ffb13b 0%, #FF2525 74%);
        background-clip: text !important;
        -webkit-background-clip: text !important;
        color: transparent !important;
    }

</style>

<style>
    /* Основное содержание */
    .main {
        margin-left: 15%;
        padding: 0 10px;
    }

    .dropdown-container {
        display: none;
        background-color:#dcdcdc;
        padding: 5px;
    }

    /* Необязательно: стиль курсора вниз значок */
    .fa-caret-down {
        float: right;
        padding-right: 8px;
    }

</style>
<style>
    .sidenav {
        height: 100%;
        width: 15%;
        position: fixed;
        z-index: 1;
        top: 0;
        left: 0;
        background-color: #eaeaea;
        overflow-x: hidden;
        padding-top: 20px;
    }

    .sidenav a, .dropdown-btn {
        padding: 6px 8px 6px 16px;
        text-decoration: none;
        font-size: 16px;
        color: #343434;
        display: block;
        border: none;
        background: none;
        width:100%;
        text-align: left;
        cursor: pointer;
        outline: none;
    }
    .sidenav a:hover, .dropdown-btn:hover {
        background-image: linear-gradient(147deg, #ffb13b 0%, #FF2525 74%);
        border-radius: 10px 10px 0px 0px;
        color: white;
        width: 100%;
    }
    .sidenav .active_sprint {
        background-image: linear-gradient(147deg, #ffb13b 0%, #FF2525 74%);
        border-radius: 10px 10px 0px 0px ;
        color: white ;
        width: 100% ;
    }
</style>

{{--ADD--}}
<style>
    .addStyleColumns{
        padding-bottom: 0.2rem !important;
        padding-top: 0.2rem !important;
        text-decoration: none;
    }
</style>

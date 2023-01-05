<style>

    body {
        overflow: hidden;
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
    .buttons-container-head{
        background-color: rgba(76, 175, 237, 0.86);
        padding-top: 3px;
        min-height: 3px;
    }
    .buttons-container {
        padding-top: 10px;
        min-height: 100px;
    }




    div.myTable {
        width: 100%;
        text-align: left;
        border-collapse: collapse;
    }
    .divTable.myTable .divTableCell, .divTable.myTable .divTableHead {
        padding: 5px 10px;
    }
    .divTable.myTable .divTableRow:nth-child(even) {
        background: #e8e8e8;
    }
    .divTable.myTable .divTableHeading {
        background-image: linear-gradient(135deg, #c3cfe2 0%, #f5f7fa 100%);
    }
    .divTable.myTable .divTableHeading .divTableHead {
        font-weight: bold;
        text-align: left;
    }
    /* DivTable.com */
    .divTable{ display: table; }
    .divTableRow { display: table-row; }
    .divTableHeading { display: table-header-group;}
    .divTableCell, .divTableHead { display: table-cell;}
    .divTableHeading { display: table-header-group;}
    .divTableFoot { display: table-footer-group;}
    .divTableBody { display: table-row-group;}

</style>

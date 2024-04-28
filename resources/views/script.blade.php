<script>
    /*Нажатие */
    let dropdown = document.getElementsByClassName("dropdown-btn");
    let i;

    for (i = 0; i < dropdown.length; i++) {
        dropdown[i].addEventListener("click", function() {
            this.classList.toggle("active");
            let dropdownContent = this.nextElementSibling;
            if (dropdownContent.style.display === "block") {
                dropdownContent.style.display = "none";
            } else {
                dropdownContent.style.display = "block";
            }
        });
    }



    /*Кнопки*/
    window.document.getElementById(item_sidenav).classList.add('active_sprint')
    if (item_sidenav.replace(/[^+\d]/g, '') > 1 && item_sidenav.replace(/[^+\d]/g, '') <= 6){
        this_click(window.document.getElementById('btn_1'))
    }

    function this_click(btn){
        btn.classList.toggle("active");
        let dropdownContent = btn.nextElementSibling;
        if (dropdownContent.style.display === "block") {
            dropdownContent.style.display = "none";
        } else {
            dropdownContent.style.display = "block";
        }
    }
</script>

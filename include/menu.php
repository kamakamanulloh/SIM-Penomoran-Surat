

<nav class="blue darken-1">
    <div class="nav-wrapper">
       
        <!-- Menu on medium and small screen END -->

        <!-- Menu on large screen START -->
        <ul class="center hide-on-med-and-down" id="nv">
            <li><a href="./" class="ams hide-on-med-and-down">Pemkot Surabaya</a></li>
             <li class="right" style="margin-right: 10px;"><a class="dropdown-button" href="#!" data-activates="logout"><i class="material-icons">account_circle</i> <?php echo $_SESSION['nama']; ?><i class="material-icons md-18">arrow_drop_down</i></a></li>
              <ul id='logout' class='dropdown-content'>
                  
                    <li><a href="logout.php"><i class="material-icons">settings_power</i> Logout</a></li>
                </ul>
           
    </div>
</nav>


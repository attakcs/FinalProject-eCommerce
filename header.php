<?php
$pageCode = GetPageCode();
?>
<header>
<!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top text-white">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav">
                <li class="nav-item ml-2">
                    <a class="nav-link <?= $pageCode === 'home'?'active':''?>" href="/"><img id="navbarLogo" src="assets/logo_new.png" alt="logo"></a>
                </li>
                <li class="nav-item ml-2 mt-2">
                    <a class="nav-link <?= $pageCode === 'product-catalog'?'active':''?>" href="/Product-Catalog">Shop</a>
                </li>
                <li class="nav-item ml-2 mt-2">
                    <a class="nav-link <?= $pageCode === 'contact-us'?'active':''?>" href="/Contact-Us">Contact</a>
                </li>
                <li class="nav-item ml-2 mt-2">
                    <?php if(IsCustomer()){?>
                    <a class="nav-link <?= $pageCode === 'my-credit-cards'?'active':''?>" href="/My-Credit-Cards">My Credit Cards</a>
                </li>
                <li class="nav-item ml-2 mt-2">
                    <a class=" nav-link <?= $pageCode === 'my-invoices'?'active':''?>" href="/My-Invoices">My Invoices</a>
                    <?php }?>
                </li>
                <li class="nav-item ml-2 mt-2">
                    <?php if(IsAdmin()){?>
                    <a class="nav-link <?= $pageCode === 'admin-panel'?'active':''?>" href="/Admin-Panel">Admin Panel</a>
                    <?php }?>
                </li>
            </ul>
        </div>

        <div class="nav-item">

            <span class="navbar-text text-right mr-5"><?= \GetUserName() ?></span>

            <span><a class="cart text-white mr-3" id="cartCounter">0</a><a href="/My-Cart"><img id="cartNavImage" src="/assets/icons/cart.png" alt=""></a></span>

        </div>

        <div class="dropdown ml-2">
            <button class="btn btn-dark dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <img id="userNavImage" src="/assets/icons/user.png" alt="">
            </button>
            <div class="dropdown-menu dropdown-menu-right mt-2" aria-labelledby="dropdownMenuButton">
                <?php if(IsLoggedIn()){?>
                    <img class="rounded mx-auto d-block mb-3" id="navbarProfilePicture" src="/api/User/Photo/">
                    <button class="btn btn-sm btn-outline-info ml-2" onclick="location.href='/Profile'">Profile</button>
                    <button class="btn btn-sm btn-outline-info" onclick="Logout()">Logout</button>
                <?php }else{?>
                    <button class="<?= $pageCode === 'register'?'active':''?> btn btn-sm btn-outline-info ml-2" onclick="location.href='/Register'">Register</button>
                    <button class="<?= $pageCode === 'login'?'active':''?> btn btn-sm btn-outline-info" onclick="location.href='/Login'">Login</button>
                <?php } ?>
            </div>
        </div>   
    </nav>
    <!-- NAVBAR ENDS -->
</header>
<script>
    UpdateCartDisplay();
</script>
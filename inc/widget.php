<div class="widget-profile" id="widget_profile">
    <img class="widget-profile-image profile-image" src="img/users/<?=$_SESSION['user']['image'];?>" />
    <div class="widget-profile-content">
        <p class="widget-profile-name"><?=$_SESSION['user']['name']['first'];?> <?=$_SESSION['user']['name']['last'];?></p>
        <ul class="widget-profile-menu">
            <li><a href="?page=profile">Meu perfil</a></li>
            <li>|</li>
            <li><a href="?page=logout">Desconectar</a></li>
        </ul>
    </div>
</div>
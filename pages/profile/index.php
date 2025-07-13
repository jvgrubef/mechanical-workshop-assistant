<div class="container">
    <div class="header">
        <h1 class="title">Perfil</h1>
    </div>
    <div class="content">
        <form id="user" class="order_itens">
            <div class="order_client">
                <div class="profile_image">
                    <img class="profile-image" src="img/users/<?=$_SESSION['user']['image'];?>" alt="<?= $_SESSION['user']['username'];?>"/>
                    <button type="button" name="upload_image">
                        <?php include ('img/icons/camera.svg');?>
                    </button>
                </div>
                <label>
                    <p>Nome</p>
                    <input type="text" name="user_first_name" placeholder="Fulano..." required value="<?= $_SESSION['user']['name']['first'];?>">
                </label>
                <label>
                    <p>Sobrenome</p>
                    <input type="text" name="user_last_name" placeholder="de..." required value="<?= $_SESSION['user']['name']['last'];?>">
                </label>
            </div>
            <div class="order_name">
                <label>
                    <p>login</p>
                    <input type="text" name="name" placeholder="username" disabled value="<?= $_SESSION['user']['username'];?> - inalterável">
                </label>
                <label>
                    <p>Senha (Deixe vazio para não alterar)</p>
                    <input type="password" name="new_password" placeholder="********"></textarea>
                </label>
                <label style="display: none;">
                    <p>Confirme a senha</p>
                    <input type="password" name="confirm_password" placeholder="********"></textarea>
                </label>
                <label style="display: none;">
                    <p>Digite sua senha atual</p>
                    <input type="password" name="current_password" placeholder="********"></textarea>
                </label>
            </div>
            <div class="order_footer">
                <button type="submit">✔</button>
            </div>
        </form>
    </div>
</div>

<script><?php include('js/profile.js');?></script>
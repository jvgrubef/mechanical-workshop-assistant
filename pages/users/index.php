<div class="container">
    <div class="header">
        <h1 class="title">Usuários</h1>
    </div>
    <div class="content">
        <nav class="menu">
            <button type="button" id="register_new" class="button">Novo Usuário</button>
            <input type="text" id="search" class="button" placeholder="Pesquisar...">
            <ul id="pagination"></ul>
        </nav>
        <ul class="min list" id="users_list"></ul>
    </div>
</div>

<div class="float-box"  id="register">
    <div class="float-box-content">
        <div class="float-box-header">
            <h2>Registro</h2>
            <button class="button close">
                <?php include('./img/icons/close.svg');?>
            </button>
        </div>
        <form class="form-content">
            <label class="form-input-text">
                <p>Nome de usuário</p>
                <input type="text" name="username" placeholder="username..." required>
            </label>
            <label class="form-input-text">
                <p>Nome</p>
                <input type="text" name="first_name" placeholder="Fulano..." required>
            </label>
            <label class="form-input-text">
                <p>Sobrenome</p>
                <input type="text" name="last_name" placeholder="de..." required>
            </label>
            <label class="form-input-text">
                <p>Senha</p>
                <input type="password" name="new_password" placeholder="********" required>
            </label>
            <label class="form-input-text" style="display: none;">
                <p>Confirme a senha</p>
                <input type="password" name="confirm_password" placeholder="********" required>
            </label>
            <label class="form-input-text">
                <p>Nivel administrativo</p>
                <select name="admin_level" require>
                    <option value="n" disabled selected>Selecione</option>
                    <option value="0">Assistente</option>
                    <option value="1">Funcionário</option>
                    <option value="2">Gerente</option>
                    <option value="3">Administrador</option>
                </select>
            </label>
            <input type="hidden" name="action" value="new">
            <input type="hidden" name="id" value="">
            <div class="parallel">
                <button class="form-button" type="button" name="insert">Adicionar</button>
                <button class="form-button" type="button" name="edit" style="display: none;">Alterar</button>
                <button class="form-button" type="button" name="delete" style="display: none;">Apagar</button>
            </div>
        </form>
    </div>
</div>

<script src="js/users.js"></script>
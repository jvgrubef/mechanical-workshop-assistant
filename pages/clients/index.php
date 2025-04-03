<div class="container">
    <div class="header">
        <h1 class="title">Clientes</h1>
    </div>
    <div class="content">
        <nav class="menu">
            <button type="button" id="register_new" class="button">Novo Cliente</button>
            <input type="text" id="search" class="button" placeholder="Pesquisar...">
            <ul id="pagination"></ul>
        </nav>
        <ul class="min list" id="client_list"></ul>
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
                <p>Nome</p>
                <input type="text" name="name" placeholder="Fulano ..." required>
            </label>
            <label class="form-input-text">
                <p>Telefone (Separados por Vírgula)</p>
                <input type="text" name="phones" placeholder="(00) 90000-0000" data-mask="phone">
                <div class="alias"></div>
            </label>
            <label class="form-input-text">
                <p>Endereço</p>
                <input type="text" name="address" placeholder="Avenida ZZ, 0000 ...">
            </label>
            <label class="form-input-text">
                <p>Pontuação</p>
                <input type="number" name="rating" min="0" max="5" value="3" placeholder="3" required>
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

<script src="js/client.js"></script>

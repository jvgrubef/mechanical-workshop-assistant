<div class="container">
    <div class="header">
        <h1 class="title">Marcas & Modelos</h1>
    </div>
    <div class="content">
        <nav class="menu">
            <button type="button" id="register_new" class="button">Novo Item</button>
            <button type="button" id="toggle_inventory" class="button">Ver o Estoque</button>
            <input type="text" id="search" class="button" placeholder="Pesquisar...">
            <ul id="pagination"></ul>
        </nav>
        <ul class="min list" id="models_list"></ul>
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
                <p>Modelo</p>
                <input type="text" name="name" placeholder="Modelo X..." required>
            </label>
            <label class="form-input-text">
                <p>Marca</p>
                <input type="text" name="brand" placeholder="Marca Y..." required>
            </label>
            <label class="form-input-text">
                <p>Equipamento</p>
                <input list="equipment_list" name="type">
                <datalist id="equipment_list">
                    <option value="motosserra">
                    <option value="soprador">
                    <option value="lavadoura">
                    <option value="pulverizador">
                    <option value="roçadeira">
                    <option value="grupo gerador">
                    <option value="motor bomba">
                    <option value="outro">
                </datalist>
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

<script src="js/models.js"></script>

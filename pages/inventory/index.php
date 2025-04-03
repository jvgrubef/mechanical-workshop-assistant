<?php 
    if (isset($_GET['models'])) {
        include('models.php');
        exit;
    };
?>

<div class="container">
    <div class="header">
        <h1 class="title">Estoque</h1>
    </div>
    <div class="content">
        <nav class="menu">
            <button type="button" id="register_new" class="button">Novo Item</button>
            <button type="button" id="toggle_models" class="button">Ver Marcas & Modelos</button>
            <input type="text" id="search" class="button" placeholder="Pesquisar...">
            <input type="text" id="search_model" class="button" placeholder="Para o modelo...">
            
            <ul id="pagination"></ul>
        </nav>
        <ul class="min list" id="inventory_list"></ul>
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
                <p>Descrição</p>
                <input type="text" name="description" placeholder="Filtro..." required>
            </label>
            <label class="form-input-text">
                <p>Valor</p>
                <input type="text" name="price_fake" placeholder="R$ 0,00" required>
            </label>
            <label class="form-input-text">
                <p>Quantidade</p>
                <input type="number" name="quantity" min="0" value="100" required>
            </label>
            <label class="form-input-text">
                <p>Compatível apenas com (opcional): </p>
                <input type="text" name="compatible_display" min="0" placeholder="Clique para selecionar" readonly>
            </label>

            <input type="hidden" name="action" value="new">
            <input type="hidden" name="price" value="">
            <input type="hidden" name="compatible" value="">
            <input type="hidden" name="id" value="">

            <div class="parallel">
                <button class="form-button" type="button" name="insert">Adicionar</button>
                <button class="form-button" type="button" name="edit" style="display: none;">Alterar</button>
                <button class="form-button" type="button" name="delete" style="display: none;">Apagar</button>
            </div>
        </form>
    </div>
</div>

<div class="float-box" id="models">
    <div class="float-box-content">
        <div class="float-box-header">
            <h2>Selecionar Modelo</h2>
            <button class="button close">
                <?php include('./img/icons/close.svg');?>
            </button>
        </div>
        <form class="form-content">
            <label class="form-input-text">
                <p>Pesquisa por modelo</p>
                <input type="text" name="search" placeholder="Modelo..." required>
            </label>
            <ul class="models-list list min"></ul>
            <ul class="form-pagination pagination-list"></ul>
        </form>
    </div>
</div>

<script src="js/inventory.js"></script>
<?php 
    if (is_numeric($_GET['view']) || $_GET['view'] === 'new') {
        include('view.php');
        exit;
    };
?>

<div class="container">
    <div class="header">
        <h1 class="title">Orçamento</h1>
    </div>
    <div class="content">
        <nav class="menu">
            <button type="button" id="only_client" style="display: none" class="button">Serviços do cliente: <b>Carlos</b> ×</button>
            <button type="button" id="register_new" class="button">Novo Orçamento</button>
            <input type="text" id="search" class="button" placeholder="Pesquisar...">
            <ul id="pagination"></ul>
        </nav>
        <ul class="min list" id="orders_list"></ul>
    </div>
</div>

<script src="js/orders.js"></script>
<div class="container">
    <div class="header">
        <h1 class="title">Livro Caixa</h1>
    </div>
    <div class="content">
        <nav class="menu">
            <button type="button" id="register_new" class="button">Novo Registro</button>
            <input type="date" id="date" class="button">
            <span class="separator"></span>
            <label class="button">
                <p id="total_balance">Balanço: 0,00</p>
            </label>
            <label class="button">
                <p id="month_balance">Balanço do mês: 0,00</p>
            </label>
        </nav>
        <ul class="min list" id="cashbook_list"></ul>
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
                <input type="text" name="description" placeholder="Entrada..." required>
            </label>
            <label class="form-input-text">
                <p>Valor</p>
                <input type="text" name="amount_fake" placeholder="R$ 0,00" required>
            </label>
            <label class="form-input-text">
                <p>Data</p>
                <input type="date" name="transaction_date" required>
            </label>
            <label>
                <p>Tipo</p>
            </label>
            <div class="parallel">
                <label class="form-input-radio">
                    <input type="radio" name="transaction_type" value="in" checked>
                    <p>Entrada</p>
                </label>
                <label class="form-input-radio">
                    <input type="radio" name="transaction_type" value="out">
                    <p>Saída</p>
                </label>
            </div>
            <input type="hidden" name="action" value="insert">
            <input type="hidden" name="amount" value="">
            <input type="hidden" name="id" value="">
            <div class="parallel">
                <button class="form-button" type="button" name="insert">Adicionar</button>
                <button class="form-button" type="button" name="edit" style="display: none;">Alterar</button>
                <button class="form-button" type="button" name="delete" style="display: none;">Apagar</button>
            </div>
        </form>
    </div>
</div>

<script src="js/cashbook.js"></script>

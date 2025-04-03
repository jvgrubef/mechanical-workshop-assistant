<div class="container">
    <div class="header">
        <h1 class="title">Orçamento</h1>
    </div>
    <div class="content">
        <form id="order_itens" class="order_itens">
            <div class="order_client">
                <label>
                    <p>Título</p>
                    <input type="text" name="title" placeholder="Exemplo: Conta de energia, internet, etc..." required>
                </label>
            </div>
            <div class="order_name">
                <label>
                    <p>Detalhes</p>
                    <textarea name="details" placeholder="Descreva... (opcional)" style="min-height: calc(100vh - 310px);"></textarea>
                </label>
            </div>
            <div class="order_footer">
                <label>
                    <p>Importância</p>
                    <select name="importance">
                        <option value="0">Baixa</option>
                        <option value="1">Média</option>
                        <option value="2">Alta</option>
                        <option value="3">Muito Alta</option>
                    </select>
                </label>
                <label style="margin-right: auto;">
                    <p>Período</p>
                    <select name="period">
                        <option value="0">No dia:</option>
                        <option value="1">Entre:</option>
                        <option value="2">Todo mês dia:</option>
                        <option value="3">Indeterminado</option>
                    </select>
                </label>
                <label>
                    <p>Data Inicial</p>
                    <input type="date" name="date" style="width: 155px;">
                </label>

                <label class="hide">
                    <p>Dia</p>
                    <select name="day"  disabled>
                        <?php for ($i=1; $i < 31; $i++): ?>
                            <option value="<?= $i; ?>"><?= $i <= 9 ? "0$i" :$i; ?></option>
                        <?php endfor;?>
                    </select>
                </label>
                <label class="hide">
                    <p>Data Final</p>
                    <input type="date" name="deadline" style="width: 155px;" disabled>
                </label>
                <input type="hidden" name="id">
                <input type="hidden" name="action" value="new">
                <button type="submit" name="confirm">✔</button>
                <button type="button" name="delete" style="padding: 10px;"><?php include('./img/icons/trash.svg');?></button>
            </div>            
        </form>
    </div>
</div>

<div class="float-box" id="client">
    <div class="float-box-content">
        <div class="float-box-header">
            <h2>Selecionar Cliente</h2>
            <button class="button close">
                <?php include('./img/icons/close.svg');?>
            </button>
        </div>
        <form class="form-content">
            <label class="form-input-text">
                <p>Pesquisar por nome</p>
                <input type="text" name="search" placeholder="Fulano ..." required>
            </label>
            <ul class="client-list list min"></ul>
            <ul class="form-pagination pagination-list"></ul>
        </form>
    </div>
</div>

<script>
    const reminder = '<?= $_GET['view']; ?>';
</script>
<script src="js/reminders.view.js"></script>
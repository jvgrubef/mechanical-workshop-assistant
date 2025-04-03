<div class="container">
    <div class="header">
        <h1 class="title">Orçamento</h1>
    </div>
    <div class="content">
        <form id="order_itens" class="order_itens">
            <div class="order_client">
                <label>
                    <p>Cliente</p>
                    <input type="text" name="client_name" placeholder="Clique para selecionar" required readonly>
                </label>
                <label>
                    <p>Contato</p>
                    <input type="text" name="client_phones" placeholder="00 9 0000-0000" required readonly>
                </label>
            </div>
            <div class="order_name">
                <label>
                    <p>Motor</p>
                    <input type="text" name="name" placeholder="Modelo do motor" required  autocomplete="off">
                    <div class="custom-datalist" name="datalist_brands"></div>
                </label>
                <label>
                    <p>Detalhes</p>
                    <textarea name="details" placeholder="Exemplo: Sabre com corrente, rabo, faca, etc..."></textarea>
                </label>
            </div>
            <label>
                <p style="display: flex;"><span>Itens</span><a class="add-from-iventory" style="margin-left:auto">Adicionar via Estoque</a></p>
            </label>
            <div class="order_itens_list">
                <label>
                    <input type="text" name="items[]" placeholder="Descrição, exemplo: Mão de obra" required/>
                    <input type="text" name="values[]" value="R$ 0,00" placeholder="R$ 0,00" required/>
                    <button type="button" name="add_item">+</button>
                </label>
            </div>
            <div class="order_footer">
                <label style="margin-right: auto;">
                    <p>Estado</p>
                    <select name="status">
                        <option value="0">Em espera</option>
                        <option value="1">Autorizado</option>
                        <option value="2">Em andamento</option>
                        <option value="3">A receber</option>
                        <option value="4">Finalizado</option>
                        <option value="5">Cancelado</option>
                    </select>
                </label>
                <label style="margin-right: auto;">
                    <p>Data</p>
                    <input type="date" name="date">
                </label>
                <label>
                    <p>Total</p>
                    <input type="text" name="value" value="R$ 0,00" placeholder="R$ 0,00" readonly />
                </label>
                <input type="hidden" name="id">
                <input type="hidden" name="client_id">
                <input type="hidden" name="action" value="new">
                <button type="submit" name="confirm_order">✔</button>
                <button type="button" name="delete_order" style="padding: 10px;"><?php include('./img/icons/trash.svg');?></button>
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
    const order = '<?= $_GET['view']; ?>';
</script>
<script src="js/orders.view.js"></script>
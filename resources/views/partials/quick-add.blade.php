<div class="modal fade" id="globalQuickAdd" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0"><h5 class="modal-title">Quick add</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body quick-add-grid">
                <a href="{{ route('transactions', ['new' => 'expense']) }}" class="quick-action expense"><i class="bi bi-arrow-up-right"></i><span>Expense</span></a>
                <a href="{{ route('transactions', ['new' => 'income']) }}" class="quick-action income"><i class="bi bi-arrow-down-left"></i><span>Income</span></a>
                <a href="{{ route('transactions', ['new' => 'transfer']) }}" class="quick-action transfer"><i class="bi bi-arrow-left-right"></i><span>Transfer</span></a>
                <a href="{{ route('loans') }}" class="quick-action"><i class="bi bi-cash-stack"></i><span>Loan</span></a>
                <a href="{{ route('savings') }}" class="quick-action"><i class="bi bi-piggy-bank"></i><span>Saving</span></a>
                <a href="{{ route('budgets') }}" class="quick-action"><i class="bi bi-bullseye"></i><span>Budget</span></a>
            </div>
        </div>
    </div>
</div>

@props([
    'title' => 'Items',
    'data' => [],
    'selected' => [],
    'onSelectionChange' => '',
    'name' => 'selected_ids'
])

<div {{ $attributes->merge(['class' => 'multi-selector-container']) }}>
    <div class="multi-selector-header d-flex align-items-center mb-2">
        <button type="button" class="btn btn-primary btn-sm toggle-arrow me-3">&#11167;</button>
        <span class="selected-count">0 {{ $title }} Currently Selected, Click the arrow to modify.</span>
    </div>
    <div class="multi-selector-body border p-2 col-md-12" style="display: none;">
        <div class="select-box col-md-5">
            <label class="form-label">Unselected</label>
            <select class="form-select unselected-list" multiple size="5">
                @foreach($data as $id => $label)
                    @unless(in_array($id, $selected))
                        <option value="{{ $id }}">{{ $label }}</option>
                    @endunless
                @endforeach
            </select>
            <div class="mt-2 d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-soft-dark select-all-unselected">Select All</button>
                <button type="button" class="btn btn-sm btn-soft-dark unselect-all-unselected">Unselect All</button>
            </div>
        </div>
        <div class="col-md-2 d-flex justify-content-center align-items-center">
            <div class="d-flex flex-column align-items-center">
                <button type="button" class="btn btn-primary move-to-selected mb-2">&gt;&gt;</button>
                <button type="button" class="btn btn-primary move-to-unselected">&lt;&lt;</button>
            </div>
        </div>
        <div class="select-box col-md-5">
            <label class="form-label">Selected</label>
            <select class="form-select selected-list" multiple size="5" name="{{ $name }}">
                @foreach($data as $id => $label)
                    @if(in_array($id, $selected))
                        <option value="{{ $id }}" selected>{{ $label }}</option>
                    @endif
                @endforeach
            </select>
            <div class="mt-2 d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-soft-dark select-all-selected">Select All</button>
                <button type="button" class="btn btn-sm btn-soft-dark unselect-all-selected">Unselect All</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('loading multiselect-php')
    const containers = document.querySelectorAll('.multi-selector-container');
    
    containers.forEach(container => {
        const multiSelectorBody = container.querySelector('.multi-selector-body');
        const toggleArrow = container.querySelector('.toggle-arrow');
        const selectedCount = container.querySelector('.selected-count');
        const unselectedList = container.querySelector('.unselected-list');
        const selectedList = container.querySelector('.selected-list');
        const title = '{{ $title }}';
        const onSelectionChange = '{{ $onSelectionChange }}';

        const updateSelection = () => {
            const selectedIds = Array.from(selectedList.querySelectorAll('option')).map(option => option.value);
            
            // Update count message
            selectedCount.textContent = `${selectedIds.length} ${title} Currently Selected, Click the arrow to modify.`;
            
            // Trigger callback if defined
            if (onSelectionChange && window[onSelectionChange]) {
                window[onSelectionChange](selectedIds);
            }
        };

        // Initialize with already selected items
        updateSelection();

        // Toggle show/hide of the multi-selector
        toggleArrow.addEventListener('click', function() {
            const isVisible = multiSelectorBody.style.display !== 'none';
            multiSelectorBody.style.display = isVisible ? 'none' : 'flex';
            toggleArrow.innerHTML = isVisible ? '&#11167;' : '&#11165;';
        });

        // Move to selected
        container.querySelector('.move-to-selected').addEventListener('click', function() {
            const selectedOptions = unselectedList.querySelectorAll('option:checked');
            selectedOptions.forEach(option => {
                selectedList.appendChild(option);
            });
            updateSelection();
        });

        // Move to unselected
        container.querySelector('.move-to-unselected').addEventListener('click', function() {
            const selectedOptions = selectedList.querySelectorAll('option:checked');
            selectedOptions.forEach(option => {
                unselectedList.appendChild(option);
            });
            updateSelection();
        });

        // Select All in Unselected
        container.querySelector('.select-all-unselected').addEventListener('click', function() {
            const options = unselectedList.querySelectorAll('option');
            options.forEach(option => option.selected = true);
        });

        // Unselect All in Unselected
        container.querySelector('.unselect-all-unselected').addEventListener('click', function() {
            const options = unselectedList.querySelectorAll('option');
            options.forEach(option => option.selected = false);
        });

        // Select All in Selected
        container.querySelector('.select-all-selected').addEventListener('click', function() {
            const options = selectedList.querySelectorAll('option');
            options.forEach(option => option.selected = true);
        });

        // Unselect All in Selected
        container.querySelector('.unselect-all-selected').addEventListener('click', function() {
            const options = selectedList.querySelectorAll('option');
            options.forEach(option => option.selected = false);
        });
    });
});
</script>
@endpush
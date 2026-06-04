@php
    $variant = $variant ?? 'kanban';
    $message = $message ?? ($variant === 'table' ? 'No deals match the current filters.' : 'No deals');
@endphp

@if ($variant === 'table')
    <tr>
        <td colspan="5" class="px-5 py-16 text-center">
            <div class="flex flex-col items-center gap-2 text-slate-400 dark:text-slate-500">
                <svg class="w-8 h-8 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="text-sm italic">{{ $message }}</span>
            </div>
        </td>
    </tr>
@else
    <div class="flex-1 flex flex-col items-center justify-center text-center p-8 text-slate-400 dark:text-slate-600 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-xl mt-1">
        <svg class="w-6 h-6 mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <span class="text-xs">{{ $message }}</span>
    </div>
@endif
<?php

use App\Models\Bookings;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    /** @var string */
    public $search = '';

    /**
     * Reset pagination whenever the search term changes.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Live-search the booking table by guest name or booking number.
     */
    public function getBookingsProperty(): LengthAwarePaginator
    {
        return Bookings::query()
            ->when($this->search !== '', function ($query) {
                $query->where('GuestName', 'like', '%' . $this->search . '%')
                    ->orWhere('BookNo', 'like', '%' . $this->search . '%');
            })
            ->orderByDesc('u_entdt')
            ->paginate(10);
    }
};
?>

<div>
    <div class="input-group mb-3">
        <span class="input-group-text">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
            </svg>
        </span>
        <input
            type="search"
            wire:model.live.debounce.300ms="search"
            class="form-control"
            placeholder="Search by guest name or booking no..."
            aria-label="Search bookings">
        @if ($search !== '')
            <button class="btn btn-outline-secondary" type="button" wire:click="$set('search', '')">Clear</button>
        @endif
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Booking No</th>
                    <th scope="col">Guest Name</th>
                    <th scope="col">Property</th>
                    <th scope="col">Booking Date</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->bookings as $booking)
                    <tr>
                        <td>{{ $this->bookings->firstItem() + $loop->index }}</td>
                        <td>{{ $booking->BookNo }}</td>
                        <td>{{ $booking->GuestName }}</td>
                        <td>{{ $booking->Property_ID }}</td>
                        <td>{{ $booking->vdate ? \Carbon\Carbon::parse($booking->vdate)->format('d M Y') : '—' }}</td>
                        <td>
                            @if ($booking->Cancel)
                                <span class="badge bg-danger">Cancelled</span>
                            @elseif ($booking->ResStatus)
                                <span class="badge bg-success">{{ $booking->ResStatus }}</span>
                            @else
                                <span class="badge bg-secondary">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            No bookings found{{ $search !== '' ? ' for "' . e($search) . '"' : '' }}.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($this->bookings->hasPages())
        <div class="d-flex justify-content-end">
            {{ $this->bookings->links() }}
        </div>
    @endif
</div>

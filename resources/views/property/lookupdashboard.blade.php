@extends('property.layouts.main')
@section('main-container')
    <div class="content-body">
        <div class="container-fluid">

            <div class="card shadow-sm p-3">

                {{-- Tabs --}}
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#ldLinks" type="button" role="tab">
                            <i class="mdi mdi-link-variant me-1"></i>Quick Links
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#ldSummary" type="button" role="tab" id="ldSummaryTab">
                            <i class="mdi mdi-pulse me-1"></i>Live Summary
                        </button>
                    </li>
                </ul>

                <div class="tab-content">

                    {{-- Tab 1: Quick Links (existing cards) --}}
                    <div class="tab-pane fade show active" id="ldLinks" role="tabpanel">
                        <div class="row g-3">

                            {{-- Card 1 --}}
                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 mb-3">
                                <div class="card h-100" style="border:1px solid #000; transition:all 0.3s ease; cursor:pointer;"
                                    onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 8px 18px rgba(0,0,0,0.2)';"
                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                                    <div class="card-body text-center d-flex flex-column">
                                        <h5 class="card-title mb-3">Total Purchase</h5>
                                        <a href="{{ route('total.purchase') }}" class="btn btn-primary mt-auto">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>

                            {{-- Card 2 --}}
                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 mb-3">
                                <div class="card h-100" style="border:1px solid #000; transition:all 0.3s ease; cursor:pointer;"
                                    onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 8px 18px rgba(0,0,0,0.2)';"
                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                                    <div class="card-body text-center d-flex flex-column">
                                        <h5 class="card-title mb-3">Pending Indent</h5>
                                        <a href="{{ route('invinsights') }}#pendingIndents" class="btn btn-primary mt-auto">View
                                            Details</a>
                                    </div>
                                </div>
                            </div>

                            {{-- Card 3 --}}
                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 mb-3">
                                <div class="card h-100" style="border:1px solid #000; transition:all 0.3s ease; cursor:pointer;"
                                    onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 8px 18px rgba(0,0,0,0.2)';"
                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                                    <div class="card-body text-center d-flex flex-column">
                                        <h5 class="card-title mb-3">Pending Purchase Order</h5>
                                        <a href="{{ route('invinsights') }}#pendingPOs" class="btn btn-primary mt-auto">View
                                            Details</a>
                                    </div>
                                </div>
                            </div>

                            {{-- Card 4 --}}
                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 mb-3">
                                <div class="card h-100" style="border:1px solid #000; transition:all 0.3s ease; cursor:pointer;"
                                    onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 8px 18px rgba(0,0,0,0.2)';"
                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                                    <div class="card-body text-center d-flex flex-column">
                                        <h5 class="card-title mb-3">Receiver VS Pending Material</h5>
                                        <a href="{{ route('receiverpendingmaterial') }}" class="btn btn-primary mt-auto">View
                                            Details</a>
                                    </div>
                                </div>
                            </div>

                            {{-- Card 5 --}}
                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 mb-3">
                                <div class="card h-100" style="border:1px solid #000; transition:all 0.3s ease; cursor:pointer;"
                                    onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 8px 18px rgba(0,0,0,0.2)';"
                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                                    <div class="card-body text-center d-flex flex-column">
                                        <h5 class="card-title mb-3">Supplier Wise Purchase</h5>
                                        <a href="{{ route('invinsights') }}#supplierWise" class="btn btn-primary mt-auto">View
                                            Details</a>
                                    </div>
                                </div>
                            </div>

                            {{-- Card 6 --}}
                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 mb-3">
                                <div class="card h-100" style="border:1px solid #000; transition:all 0.3s ease; cursor:pointer;"
                                    onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 8px 18px rgba(0,0,0,0.2)';"
                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                                    <div class="card-body text-center d-flex flex-column">
                                        <h5 class="card-title mb-3">Delay Delivery</h5>
                                        <a href="{{ route('delaydeliveryreport') }}" class="btn btn-primary mt-auto">View Details</a>
                                    </div>
                                </div>
                            </div>

                            {{-- Card 7 --}}
                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 mb-3">
                                <div class="card h-100" style="border:1px solid #000; transition:all 0.3s ease; cursor:pointer;"
                                    onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 8px 18px rgba(0,0,0,0.2)';"
                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                                    <div class="card-body text-center d-flex flex-column">
                                        <h5 class="card-title mb-3">Purchase Trend</h5>
                                        <a href="{{ route('invinsights') }}#trend" class="btn btn-primary mt-auto">View
                                            Details</a>
                                    </div>
                                </div>
                            </div>

                            {{-- Card 8 --}}
                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 mb-3">
                                <div class="card h-100" style="border:1px solid #000; transition:all 0.3s ease; cursor:pointer;"
                                    onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 8px 18px rgba(0,0,0,0.2)';"
                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                                    <div class="card-body text-center d-flex flex-column">
                                        <h5 class="card-title mb-3">Minus stock</h5>
                                        <a href="{{ route('invinsights') }}#minusStock" class="btn btn-primary mt-auto">View
                                            Details</a>
                                    </div>
                                </div>
                            </div>

                            {{-- Card 9 --}}
                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 mb-3">
                                <div class="card h-100" style="border:1px solid #000; transition:all 0.3s ease; cursor:pointer;"
                                    onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 8px 18px rgba(0,0,0,0.2)';"
                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                                    <div class="card-body text-center d-flex flex-column">
                                        <h5 class="card-title mb-3">Pending M.R</h5>
                                        <a href="{{ route('pendingmr') }}" class="btn btn-primary mt-auto">View Details</a>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- Tab 2: Live Summary (AJAX) --}}
                    <div class="tab-pane fade" id="ldSummary" role="tabpanel">
                        <div class="row g-3" id="ldSummaryCards">
                            <div class="col-xl-4 col-md-6">
                                <div class="card border-0 shadow-sm bg-soft-primary">
                                    <div class="card-body text-center">
                                        <h3 class="mb-0" id="ldSuppliers">—</h3>
                                        <small class="text-muted">Active Suppliers</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 col-md-6">
                                <div class="card border-0 shadow-sm bg-soft-success">
                                    <div class="card-body text-center">
                                        <h3 class="mb-0" id="ldItems">—</h3>
                                        <small class="text-muted">Purchase Items</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 col-md-12">
                                <div class="card border-0 shadow-sm bg-soft-warning">
                                    <div class="card-body text-center">
                                        <h3 class="mb-0" id="ldMr">—</h3>
                                        <small class="text-muted">MR Entries This Month</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-center text-muted py-3 d-none" id="ldSummaryError">
                            <i class="mdi mdi-wifi-off me-1"></i>Could not load summary.
                            <button type="button" class="btn btn-sm btn-outline-primary ms-2" onclick="ldFetch()">Retry</button>
                        </div>
                        <p class="text-muted small mb-0"><i class="mdi mdi-information-outline me-1"></i>Auto-refreshes every 60 seconds while this tab is open.</p>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        function ldFetch() {
            $.getJSON('{{ url("invdashboard/summary") }}', function (s) {
                $('#ldSummaryError').addClass('d-none');
                $('#ldSummaryCards').removeClass('d-none');
                if (!s) return;
                $('#ldSuppliers').text(window.hmsFmt(s.suppliers));
                $('#ldItems').text(window.hmsFmt(s.items));
                $('#ldMr').text(window.hmsFmt(s.mrThisMonth));
            }).fail(function () {
                $('#ldSummaryCards').addClass('d-none');
                $('#ldSummaryError').removeClass('d-none');
            });
        }

        $(function () {
            var loaded = false;
            $('#ldSummaryTab').on('shown.bs.tab', function () {
                if (!loaded) { ldFetch(); loaded = true; }
            });
            setInterval(function () {
                if ($('#ldSummary').hasClass('active')) ldFetch();
            }, 60000);
        });
    </script>
@endsection

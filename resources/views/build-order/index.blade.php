@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card" style="margin-bottom: 50px !important;">

                    <div class="card-header">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                        <h6>{{__('messages.BuildOrder')}}</h6>
                        </div>
                    </div>

                    <div class="card-body">
                        @include('layouts.message')
{{--                        <form method="post" role="form" id="search-form">--}}
                            <table id="leave_settings" class="table table-bordered datatable table-responsive mainTable text-center">

                                <thead class="thead-dark">
                                <tr>
                                    <th>{{__('messages.SL')}}</th>
                                    <th>{{__('messages.OrderAt')}}</th>
                                    <th>Process Time</th>
                                    <th>{{__('messages.Plugin')}}</th>
{{--                                    <th>{{__('messages.packageName')}}</th>--}}
                                    <th>{{__('messages.appName')}}</th>
                                    <th>{{__('messages.Domain')}}</th>
                                    <th>{{__('messages.buildTarget')}}</th>
                                    <th>{{__('messages.Status')}}</th>
                                    <th width="13%">{{__('messages.AllFile')}}</th>
                                    <th width="14%">{{__('messages.BuildLog')}}</th>
{{--                                    <th>Delete</th>--}}
                                </tr>
                                </thead>

                                @if(sizeof($buildOrders)>0)
                                    <tbody>
                                        @php
                                            $i=1;
                                            $currentPage = $buildOrders->currentPage();
                                            $perPage = $buildOrders->perPage();
                                            $serial = ($currentPage - 1) * $perPage + 1;
                                            $previousHistoryId = null;
                                            $previousProcessTime = null;

                                            $serial = ($buildOrders->currentPage() - 1) * $buildOrders->perPage() + 1;
                                            // Convert paginator to array for index access
                                            $buildOrdersArray = $buildOrders->values(); // reindex from 0, 1, 2...
                                        @endphp

                                        @foreach($buildOrdersArray as $index => $buildOrder)
                                            <tr>
                                                <td>
                                                    {{$serial++}}
                                                </td>
                                                <td>{{$buildOrder->created_at->format('d-M-Y')}}</td>
                                                <td>
                                                    @php
                                                        $created_at = Carbon::parse($buildOrder->process_start);
                                                        $updated_at = Carbon::parse($buildOrder->updated_at);

                                                        // Allow signed difference (can be negative)
                                                        $currentProcessTime = $created_at->diffInMinutes($updated_at, false);

                                                        // Set default if negative
                                                        $displayProcessTime = $currentProcessTime < 0 ? 0 : $currentProcessTime;
                                                    @endphp
                                                    {{number_format($displayProcessTime,2).' minutes'}}
                                                </td>
                                                <td>{{$buildOrder->build_plugin_slug}}</td>
{{--                                                <td>{{$buildOrder->package_name}}</td>--}}
                                                <td>{{$buildOrder->app_name}}</td>
                                                <td>{{$buildOrder->domain}}</td>
                                                <td>{{$buildOrder->build_target}}</td>
                                                <td>
                                                    @php
                                                        // Ensure status is a string from Enum
                                                        $status = $buildOrder->status?->value ?? 'pending';

                                                        // Softened color styles for each status (badges instead of buttons)
                                                        $statusMap = [
                                                            'failed' => ['class' => 'bg-secondary text-white', 'icon' => '❌', 'label' => 'Failed'],
                                                            'completed' => ['class' => 'bg-secondary text-white', 'icon' => '✅', 'label' => 'Completed'],
                                                            'processing' => ['class' => 'bg-secondary text-dark', 'icon' => '⏳', 'label' => 'Processing'],
                                                            'pending' => ['class' => 'bg-secondary text-dark', 'icon' => '🕒', 'label' => 'Pending'],
                                                             /*'pending' => [
                                                                'class' => ' text-dark',
                                                                'icon' => '<img src="' . asset('1.gif') . '" alt="Processing" width="50">',
                                                                'label' => 'Pending'
                                                            ],*/
                                                        ];

                                                        // Get status data or use a default
                                                        $statusData = $statusMap[$status] ?? ['class' => 'bg-secondary text-white', 'icon' => '❓', 'label' => ucfirst($status)];
                                                    @endphp

                                                    <span class="badge {{ $statusData['class'] }} shadow-sm fs-6 rounded-pill px-3 py-2 d-inline-flex align-items-center">
        <span class="me-1">{!! $statusData['icon'] !!}</span> {{ $statusData['label'] }}</span>
                                                </td>

                                                <td>
                                                    @if($buildOrder->apk_url)
                                                        <a href="{{$buildOrder->apk_url}}" download class="badge bg-dark text-white shadow-sm fs-6 rounded-pill px-3 py-2 d-inline-flex align-items-center"><span class="me-1">⏳</span> APK</a>
                                                    @endif

                                                    @if($buildOrder->aab_url)
                                                        <a href="{{$buildOrder->aab_url}}" download class="badge bg-dark text-white shadow-sm fs-6 rounded-pill px-3 py-2 d-inline-flex align-items-center"><span class="me-1">⏳</span> AAB</a>
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        // Ensure status is a string from Enum
                                                        $statusLog = $buildOrder->status?->value ?? 'pending';

                                                        // Softened color styles for each status (badges instead of buttons)
                                                        $statusMapLog = [
                                                            'failed' => ['class' => 'bg-secondary text-white', 'icon' => '❌', 'label' => 'Failed'],
                                                            'completed' => ['class' => 'bg-secondary text-white', 'icon' => '📄', 'label' => 'Completed'],
                                                            'processing' => ['class' => 'bg-secondary text-dark', 'icon' => '📄', 'label' => 'Processing'],
                                                            'pending' => ['class' => 'bg-secondary text-dark', 'icon' => '📄', 'label' => 'Pending'],
                                                        ];

                                                        // Get status data or use a default
                                                        $statusDataLog = $statusMapLog[$statusLog] ?? ['class' => 'bg-secondary text-white', 'icon' => '❓', 'label' => ucfirst($statusLog)];
                                                    @endphp

                                                    @if($buildOrder->ios_output_url)
                                                        <button type="button" class="btn btn-primary" onclick="openFileInModal('{{ $buildOrder->ios_output_url }}')">
                                                            {!! $statusDataLog['icon'] !!} View
                                                        </button>
                                                    @endif

                                                    @if($buildOrder->android_output_url)
                                                        <button type="button" class="btn btn-primary" onclick="openFileInModal('{{ $buildOrder->android_output_url }}')">
                                                            📄 View
                                                        </button>
                                                    @endif

                                                    @if($buildOrder->runner_url)
                                                        <button type="button" class="btn btn-secondary" onclick="openFileInModal('{{ $buildOrder->runner_url }}')">
                                                            📄 Runner
                                                        </button>
                                                    @endif
                                                </td>
                                                {{--<td>
                                                    @if(($buildOrder->status?->value == 'completed' || $buildOrder->status?->value == 'failed') && !$buildOrder->is_build_dir_delete && !empty($buildOrder->build_dir))
                                                        <button
                                                            title="Delete build directory"
                                                            class="btn delete-build-dir-btn"
                                                            data-build-order-id="{{ $buildOrder->id }}"
                                                        >
                                                            ❌
                                                        </button>
                                                    @endif
                                                </td>--}}
                                            </tr>
                                            @php $i++; @endphp
                                        @endforeach
                                    </tbody>
                                @endif
                            </table>
                            @if(isset($buildOrders) && count($buildOrders)>0)
                                <div class=" justify-content-right">
                                    {{ $buildOrders->links('layouts.pagination') }}
                                </div>
                            @endif
{{--                        </form>--}}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="outputModal" tabindex="-1" aria-labelledby="outputModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl"> <!-- Extra large modal -->
            <div class="modal-content shadow-lg rounded-3"> <!-- Soft shadow & rounded corners -->

                <!-- Modal Header -->
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title d-flex align-items-center" id="outputModalLabel">
                        <i class="fas fa-file-alt me-2"></i> Build Output Log
                    </h5>
                    <div>
                        <!-- Full-Screen Button -->
                        <button type="button" class="btn btn-outline-light btn-sm me-2" onclick="toggleFullScreen()">
                            <i class="fas fa-expand"></i> Fullscreen
                        </button>
                        <!-- Close Button -->
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="modal-body bg-dark text-white p-3 rounded">
                    <iframe id="outputIframe" class="w-100 border rounded bg-white shadow-sm" style="height: 500px;"></iframe>
                </div>

                <!-- Footer (Optional) -->
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>




@endsection

@section('footer.scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Find all delete buttons with the specific class
            const deleteButtons = document.querySelectorAll('.delete-build-dir-btn');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();

                    if (!confirm('Are you sure you want to delete this build directory?')) {
                        return;
                    }

                    const buildOrderId = this.dataset.buildOrderId;
                    const url = `/build-orders/${buildOrderId}/delete-dir`;

                    // Show loading state
                    this.disabled = true;
                    this.textContent = 'Deleting...';

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                alert(data.message);
                                // Hide the delete button or update UI as needed
                                this.style.display = 'none';

                                // Update any status indicators on the page
                                const statusElement = document.querySelector(`#build-status-${buildOrderId}`);
                                if (statusElement) {
                                    statusElement.textContent = 'Delete in progress';
                                }
                            } else {
                                alert('Error: ' + data.message);
                                this.disabled = false;
                                this.textContent = 'Delete Directory';
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while deleting the build directory.');
                            this.disabled = false;
                            this.textContent = 'Delete Directory';
                        });
                });
            });
        });
    </script>
    <script>
        function openFileInModal(fileUrl) {
            // Set the iframe source to the file URL
            document.getElementById("outputIframe").src = fileUrl;

            // Show the modal
            let outputModal = new bootstrap.Modal(document.getElementById('outputModal'));
            outputModal.show();
        }
    </script>

    <script>
        function toggleFullScreen() {
            let modalBody = document.querySelector("#outputModal .modal-body iframe");
            if (!document.fullscreenElement) {
                if (modalBody.requestFullscreen) {
                    modalBody.requestFullscreen();
                } else if (modalBody.mozRequestFullScreen) { /* Firefox */
                    modalBody.mozRequestFullScreen();
                } else if (modalBody.webkitRequestFullscreen) { /* Chrome, Safari, Edge */
                    modalBody.webkitRequestFullscreen();
                } else if (modalBody.msRequestFullscreen) { /* IE/Edge */
                    modalBody.msRequestFullscreen();
                }
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        }
    </script>

@endsection

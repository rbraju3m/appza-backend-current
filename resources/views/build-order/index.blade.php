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
                        <form method="post" role="form" id="search-form">
                            <table id="leave_settings" class="table table-bordered datatable table-responsive mainTable text-center">

                                <thead class="thead-dark">
                                <tr>
                                    <th>{{__('messages.SL')}}</th>
                                    <th>{{__('messages.OrderAt')}}</th>
                                    <th>{{__('messages.Plugin')}}</th>
                                    <th>{{__('messages.packageName')}}</th>
                                    <th>{{__('messages.appName')}}</th>
                                    <th>{{__('messages.Domain')}}</th>
                                    <th>{{__('messages.buildTarget')}}</th>
                                    <th>{{__('messages.Status')}}</th>
                                    <th>{{__('messages.AllFile')}}</th>
                                    <th>{{__('messages.BuildLog')}}</th>
                                </tr>
                                </thead>

                                @if(sizeof($buildOrders)>0)
                                    <tbody>
                                        @php
                                            $i=1;
                                            $currentPage = $buildOrders->currentPage();
                                            $perPage = $buildOrders->perPage();
                                            $serial = ($currentPage - 1) * $perPage + 1;
                                        @endphp
                                        @foreach($buildOrders as $buildOrder)
                                            <tr>
                                                <td>{{$serial++}}</td>
                                                <td>{{$buildOrder->created_at}}</td>
                                                <td>{{$buildOrder->build_plugin_slug}}</td>
                                                <td>{{$buildOrder->package_name}}</td>
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
                                                    @if(auth()->user()->user_type === 'DEVELOPER')

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
                                                    @endif
                                                </td>
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
                        </form>
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

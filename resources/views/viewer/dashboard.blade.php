@extends('layouts.viewer')

@section('content')
<div class="page active" id="page-dashboard">
  <div class="page-hdr">
    <div>
      <div class="page-title">Viewer Dashboard</div>
      <div class="page-sub">Read-only overview of CY {{ date('Y') }} accomplishment reports</div>
    </div>
  </div>

  <div class="card">
    <div class="card-hdr">
      <div class="card-title">Consortium Accomplishment Summary</div>
    </div>
    <div class="card-body">
      <p style="color:#4b5563;font-size:14px;line-height:1.6">
        Welcome to the SecReCo Viewer Dashboard. You have read-only access to view CVAARRD consortium submissions, consolidated reports, format templates, and member institution progress.
      </p>
    </div>
  </div>
</div>
@endsection

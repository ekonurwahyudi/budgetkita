@if(session('success'))
<div class="kt-alert kt-alert-success flex items-center gap-2 p-3 rounded-lg mb-5 bg-success/10 border border-success/20">
    <i class="ki-filled ki-check-circle text-success"></i>
    <span class="text-sm text-success">{{ session('success') }}</span>
</div>
@endif

@if(session('error'))
<div class="kt-alert kt-alert-danger flex items-center gap-2 p-3 rounded-lg mb-5 bg-danger/10 border border-danger/20">
    <i class="ki-filled ki-information-2 text-danger"></i>
    <span class="text-sm text-danger">{{ session('error') }}</span>
</div>
@endif

@if($errors->any())
<div class="kt-alert kt-alert-danger flex flex-col gap-1 p-3 rounded-lg mb-5 bg-danger/10 border border-danger/20">
    @foreach($errors->all() as $error)
    <span class="text-sm text-danger">{{ $error }}</span>
    @endforeach
</div>
@endif

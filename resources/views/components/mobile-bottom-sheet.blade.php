<!-- Mobile Bottom Sheet -->
<div id="mobileBottomSheet" class="lg:hidden" style="display:none;position:fixed;inset:0;z-index:50;">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);" onclick="toggleBottomSheet()"></div>
    <div id="bottomSheetPanel" style="position:absolute;bottom:0;left:0;right:0;background:var(--background);border-radius:16px 16px 0 0;transform:translateY(100%);transition:transform 0.3s ease;padding-bottom:env(safe-area-inset-bottom);">
        <div style="display:flex;justify-content:center;padding:12px 0 4px;">
            <div style="width:40px;height:4px;border-radius:2px;background:var(--muted-foreground);opacity:0.3;"></div>
        </div>
        <div style="text-align:center;padding-bottom:16px;">
            <span style="font-size:16px;font-weight:700;color:var(--foreground);">Lainnya</span>
        </div>
        <div style="display:flex;flex-direction:column;gap:8px;padding:0 16px 24px;">
            <a href="#" style="display:flex;align-items:center;gap:14px;padding:14px 16px;border-radius:12px;background:var(--muted);text-decoration:none;color:var(--foreground);">
                <i class="ki-filled ki-profile-circle" style="font-size:20px;color:var(--muted-foreground);"></i>
                <span style="font-size:14px;font-weight:600;">Profile</span>
            </a>
            <a href="{{ url('/operasional/persediaan') }}" style="display:flex;align-items:center;gap:14px;padding:14px 16px;border-radius:12px;background:var(--muted);text-decoration:none;color:var(--foreground);">
                <i class="ki-filled ki-parcel" style="font-size:20px;color:var(--muted-foreground);"></i>
                <span style="font-size:14px;font-weight:600;">Persediaan</span>
            </a>
            <a href="{{ url('/budidaya/pemberian-pakan') }}" style="display:flex;align-items:center;gap:14px;padding:14px 16px;border-radius:12px;background:var(--muted);text-decoration:none;color:var(--foreground);">
                <i class="ki-filled ki-coffee" style="font-size:20px;color:var(--muted-foreground);"></i>
                <span style="font-size:14px;font-weight:600;">Pemberian Pakan</span>
            </a>
        </div>
    </div>
</div>

@if($alerts->isNotEmpty())
    <div class="alerts">
        @each('alerts::tailwind.alert', $alerts, 'alert')
    </div>
@endif

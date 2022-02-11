@if($alerts->isNotEmpty())
    <div class="alerts">
        @each('alerts::bootstrap.alert', $alerts, 'alert')
    </div>
@endif

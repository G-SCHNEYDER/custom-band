<div id="bandeau_container" class="container-fluid" style="background-color: {{$banner_color}};">
    <div class="bandeau_text {if $banner_defilement}messagedefilant{/if}" style="color: {{$banner_text_color}};">
        {{$banner_message}} {if $banner_allow_emoji}{{$banner_emoji_choice}}{/if}
    </div>
</div>
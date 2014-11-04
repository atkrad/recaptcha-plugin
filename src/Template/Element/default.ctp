<?= $this->Html->script($challengeAddress, ['type' => 'text/javascript']); ?>

<noscript>
    <?=
    $this->Html->tag(
        'iframe',
        null,
        [
            'src' => $noScriptAddress,
            'height' => 300,
            'width' => 500,
            'frameborder' => 0
        ]
    ); ?>
    <br>
    <?= $this->Form->textarea('recaptcha_challenge_field', ['rows' => 3, 'cols' => 40]); ?>
    <?= $this->Form->hidden('recaptcha_response_field', ['value' => 'manual_challenge']); ?>
</noscript>

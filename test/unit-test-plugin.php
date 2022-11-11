<?php

class PluginTest extends TestCase
{
    public function test_plugin_installed() {
        activate_plugin( 'disciple-tools-post-types/disciple-tools-post-types.php' );

        $this->assertContains(
            'disciple-tools-post-types/disciple-tools-post-types.php',
            get_option( 'active_plugins' )
        );
    }
}

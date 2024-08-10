<?php

namespace LokalSo\ServeLokal;

use Illuminate\Foundation\Console\ServeCommand;
use Symfony\Component\Console\Input\InputOption;
use LokalSo\Lokal;

class ServeLokalCommand extends ServeCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'serve-lokal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Serve the application on the PHP development server with additional network options';

    /**
     * Execute the console command.
     *
     * @return int
     *
     * @throws \Exception
     */
    public function handle()
    {
        $this->serverRunningHasBeenDisplayed = false;
        return parent::handle();
    }

    /**
     * Get the host for the command.
     *
     * @return string
     */
    protected function host()
    {
        return parent::host();
    }

    /**
     * Handle the process output.
     *
     * @return callable
     */
    protected function handleProcessOutput()
    {
        return function ($type, $buffer) {
            $this->outputBuffer .= $buffer;
            $this->flushOutputBuffer();
        };
    }

    /**
     * Flush the output buffer.
     *
     * @return void
     */
    protected function flushOutputBuffer()
    {
        $lines = explode("\n", $this->outputBuffer);
        $this->outputBuffer = array_pop($lines);

        foreach ($lines as $line) {
            if (strpos($line, 'Development Server (http') !== false) {
                if ($this->serverRunningHasBeenDisplayed === false) {
                    $this->serverRunningHasBeenDisplayed = true;
                    $this->displayServerRunningMessage();
                }
            } else {
                $this->info($line);
            }
        }
    }

    /**
     * Display the server running message with additional information.
     *
     * @return void
     */
    protected function displayServerRunningMessage()
    {
        $lanAddress = $this->input->getOption('lan-address') ?? "";
        $publicAddress = $this->input->getOption('public-address') ?? "";
        $tunnelName = $this->input->getOption('tunnel-name') ?? "";

        $lokal  = new Lokal();
        $lokal->newTunnel()
            ->setName($tunnelName)
            ->setTunnelType(Lokal::TunnelTypeHTTP)
            ->setLANAddress($lanAddress)
            ->setPublicAddress($publicAddress)
            ->setLocalAddress("{$this->host()}:{$this->port()}")
            ->showStartupBanner()
            ->ignoreDuplicate()
            ->create();

        $this->components->info("Server running on [http://{$this->host()}:{$this->port()}]");
        $this->comment('  <fg=yellow;options=bold>Press Ctrl+C to stop the server</>');
        $this->newLine();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['lan-address', null, InputOption::VALUE_OPTIONAL, 'The LAN address to serve the application on', ''],
            ['public-address', null, InputOption::VALUE_OPTIONAL, 'The public address to serve the application on', ''],
            ['tunnel-name', null, InputOption::VALUE_OPTIONAL, 'The name of the tunnel being used', ''],
        ]);
    }
}
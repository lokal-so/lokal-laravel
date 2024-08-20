<?php

namespace LokalSo\ServeLokal;

use Illuminate\Foundation\Console\ServeCommand;
use Symfony\Component\Console\Input\InputOption;
use LokalSo\Lokal;
use LokalSo\Options;

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
		$cidrAllow = $this->input->getOption('cidr-allow') ?? [];
		$cidrDeny = $this->input->getOption('cidr-deny') ?? [];
		$removeRequestHeaders = $this->input->getOption('remove-request-header') ?? [];
		$removeResponseHeaders = $this->input->getOption('remove-response-header') ?? [];
		$addRequestHeaders = $this->input->getOption('add-request-header') ?? [];
		$addResponseHeaders = $this->input->getOption('add-response-header') ?? [];
		$basicAuth = $this->input->getOption('basic-auth') ?? [];
		$headerKeys = $this->input->getOption('header-key') ?? [];

		$lokal = new Lokal();
		$tunnel = $lokal->newTunnel()
			->setName($tunnelName)
			->setTunnelType(Lokal::TunnelTypeHTTP)
			->setLANAddress($lanAddress)
			->setPublicAddress($publicAddress)
			->setLocalAddress("{$this->host()}:{$this->port()}")
			->showStartupBanner()
			->ignoreDuplicate();

		$options = new Options();

		// Apply CIDR allow rules
		foreach ($cidrAllow as $cidr) {
			$options->setCIDRAllow($cidr);
		}

		// Apply CIDR deny rules
		foreach ($cidrDeny as $cidr) {
			$options->setCIDRDeny($cidr);
		}

		// Remove request headers
		foreach ($removeRequestHeaders as $header) {
			$options->removeRequestHeader($header);
		}

		// Remove response headers
		foreach ($removeResponseHeaders as $header) {
			$options->removeResponseHeader($header);
		}

		// Add request headers
		foreach ($addRequestHeaders as $header) {
			list($key, $value) = explode(':', $header, 2);
			$options->addRequestHeader(trim($key), trim($value));
		}

		// Add response headers
		foreach ($addResponseHeaders as $header) {
			list($key, $value) = explode(':', $header, 2);
			$options->addResponseHeader(trim($key), trim($value));
		}

		// Set basic auth
		foreach ($basicAuth as $auth) {
			list($username, $password) = explode(':', $auth, 2);
			$options->setBasicAuth(trim($username), trim($password));
		}

		// Set header keys
		foreach ($headerKeys as $headerKey) {
			list($key, $value) = explode(':', $headerKey, 2);
			$options->setHeaderKey(trim($key), trim($value));
		}

		$tunnel->setOptions($options);
		$tunnel->create();

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
			['cidr-allow', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'CIDR ranges to allow', []],
			['cidr-deny', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'CIDR ranges to deny', []],
			['remove-request-header', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Request headers to remove', []],
			['remove-response-header', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Response headers to remove', []],
			['add-request-header', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Request headers to add (format: "Key: Value")', []],
			['add-response-header', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Response headers to add (format: "Key: Value")', []],
			['basic-auth', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Basic auth credentials (format: "username:password")', []],
			['header-key', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Header keys to set (format: "Key: Value")', []],
		]);
	}
}

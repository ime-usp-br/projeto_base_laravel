<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;
use Laravel\Dusk\Browser;
use Throwable;
use DOMDocument;
use DOMXPath;

abstract class DuskTestCase extends BaseTestCase
{
    /**
     * Prepara para a execução do teste Dusk.
     *
     * @return void
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            static::startChromeDriver(['--port=9515']);
        }
    }

    /**
     * Cria a instância do RemoteWebDriver.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }

    /**
     * Extrai e imprime o HTML dentro da div principal e a localização da falha no teste para depuração.
     *
     * @param \Laravel\Dusk\Browser $browser A instância do navegador Dusk.
     * @param \Throwable $e A exceção capturada.
     * @throws \Throwable A exceção original é relançada.
     * @return void
     */
    protected function captureBrowserHtml(Browser $browser, Throwable $e): void
    {
        try {
            // Encontrar a linha do teste que falhou
            $testFile = null;
            $testLine = null;
            $trace = $e->getTrace();

            foreach ($trace as $frame) {
                if (isset($frame['file']) && str_contains($frame['file'], 'tests/Browser/')) {
                    $testFile = $frame['file'];
                    $testLine = $frame['line'];
                    break; // Encontramos o primeiro frame relevante
                }
            }

            // Capturar HTML
            $htmlString = $browser->driver->getPageSource();
            $containerHtml = '';
            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $htmlString, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            libxml_clear_errors();
            $xpath = new DOMXPath($dom);

            // Tentar capturar #content primeiro, senão erro interno
            if (str_contains($htmlString, "Internal Server Error")){
                 $divQuery = '//div[@class="md:flex md:items-center md:justify-between md:gap-2"]';
            } else {
                 $divQuery = '//div[@id="content"][@class="col-md-12"]';
            }
            $divNodes = $xpath->query($divQuery);
            $div = $divNodes->length > 0 ? $divNodes->item(0) : null;

            if ($div) {
                $containerHtml = $dom->saveXML($div);
            } else {
                // Fallback se não encontrar a div esperada (pode capturar o body ou nada)
                 $bodyNodes = $xpath->query('//body');
                 if ($bodyNodes->length > 0) {
                     $containerHtml = $dom->saveXML($bodyNodes->item(0));
                 } else {
                    $containerHtml = "Could not find target div (#content or error div) or body tag.";
                 }
            }

            // Imprimir informações de depuração
            echo "\n<<<DUSK_HTML_CAPTURE_START>>>\n";
            echo "Exception Message: " . $e->getMessage() . "\n";
            echo "Exception Type: " . get_class($e) . "\n";
            echo "Failing Test Line: " . ($testFile ?: 'Unknown Test File') . ":" . ($testLine ?: 'Unknown Line') . "\n";
            echo "Exception Origin: " . $e->getFile() . ":" . $e->getLine() . "\n";
            echo "Captured HTML:\n";
            echo $containerHtml;
            echo "\n<<<DUSK_HTML_CAPTURE_END>>>\n";

        } catch (\Exception $captureException) {
            echo "\n<<<DUSK_HTML_CAPTURE_FAILED>>>\n";
            echo "Failed to capture HTML. Capture Error: " . $captureException->getMessage() . "\n";
            echo "Original Exception Message: " . $e->getMessage() . "\n";
            echo "Failing Test Line (Best Guess): " . ($testFile ?: 'Unknown Test File') . ":" . ($testLine ?: 'Unknown Line') . "\n";
            echo "Exception Origin: " . $e->getFile() . ":" . $e->getLine() . "\n";
            echo "<<<DUSK_HTML_CAPTURE_FAILED_END>>>\n";
        } finally {
            throw $e;
        }
    }
}
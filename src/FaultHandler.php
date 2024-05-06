<?php

declare(strict_types=1);

namespace Woodlands\Core;

use ErrorException;
use Throwable;

class FaultHandler
{
    public static function handleError(mixed $errno, mixed $errstr, mixed $errfile, mixed $errline): void
    {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    public static function handleException(\Throwable $e): void
    {
        throw $e;
    }

    private static function getLines(Throwable $e): string
    {
        $file = file_get_contents($e->getFile());
        $lines = explode("\n", $file);
        $start = $e->getLine() - 3;
        $start = $start < 0 ? 0 : $start;
        $length = 5;
        if ($start + $length > count($lines)) {
            $length = count($lines) - $start;
        }

        $lines = array_slice($lines, $start, $length);
        $line_numbers = range($start + 1, $start + $length);

        $err_script_section = "";
        for ($i = 0; $i < count($lines); $i++) {
            $line_num = $line_numbers[$i] == $e->getLine() ? "--> {$line_numbers[$i]}" : $line_numbers[$i];
            $err_script_section .= "<span>{$line_num}</span> {$lines[$i]}\n";
        }

        return $err_script_section;
    }

    public static function displayError(\Throwable $e): void
    {
        $err_script_section = self::getLines($e);

        echo <<<HTML
      <head>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/default.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/php.min.js"></script>
        <script>hljs.highlightAll();</script>
      </head>
      <style>
      :root {
        --highlight-color: #ef4444;
        --border-color: #2c2c2c;
      }

      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }

      .container {
        color: #ffffff;
        background-color: #1a1a1a;
        font-family: Arial, sans-serif;
        width: 100%;
        min-height: 100dvh;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding-block: 3rem;
      }

      .error_boundary {
        width: 100%;
        max-width: 720px;
      }

      .error_message {
        font-size: 1.25rem;
        font-weight: bold;
        text-align: center;
        background-color: var(--highlight-color);
        color: #ffffff;
        padding: 1rem 1.5rem;
      }

      .error_stack {
        background-color: #181818;
        border: 1px solid var(--border-color);
        margin-top: 1rem;
      }

      .error_stack p {
        background-color: var(--border-color);
        font-size: 0.875rem;
        padding: 0.9rem 1.5rem;
      }

      .error_stack pre {
        padding: 1rem 1.5rem;
        white-space: pre-wrap;
        word-wrap: break-word;
        line-height: 1.5;
      }

      .highlight {
        color: var(--highlight-color);
      }
      </style>

      <div class="container">
        <div class="error_boundary">
          <div class="error_message">{$e->getMessage()}</div>
          <div class="error_stack">
            <p>Error in <b class="highlight">{$e->getFile()}</b> on line <b class="highlight">{$e->getLine()}</b></p>
            <pre> <code class="language-php">{$err_script_section}</code></pre>
            <pre>{$e->getTraceAsString()}</pre>
          </div>
        </div>
      </div>
    HTML;

        exit;
    }
}

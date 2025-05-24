<?php

namespace BasharShaeb\LaravelFlutterGenerator\Contracts;

interface GeneratorInterface
{
    /**
     * Generate code based on the provided data.
     *
     * @param array $data The data to generate code from
     * @param array $options Additional options for generation
     * @return string The generated code
     */
    public function generate(array $data, array $options = []): string;

    /**
     * Get the file extension for the generated code.
     *
     * @return string The file extension
     */
    public function getFileExtension(): string;

    /**
     * Get the output path for the generated file.
     *
     * @param string $name The name of the file
     * @return string The output path
     */
    public function getOutputPath(string $name): string;
}

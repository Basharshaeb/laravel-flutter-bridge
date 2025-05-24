<?php

namespace BasharShaeb\LaravelFlutterGenerator\Generators;

use Illuminate\Support\Str;

class WidgetGenerator extends BaseGenerator
{
    /**
     * Generate widget code based on the provided data.
     *
     * @param array $data The data to generate code from
     * @param array $options Additional options for generation
     * @return string The generated widget code
     */
    public function generate(array $data, array $options = []): string
    {
        $widgetType = $options['widget_type'] ?? 'form';
        $className = $this->toPascalCase($data['class_name']);

        return match ($widgetType) {
            'form' => $this->generateFormWidget($data, $className),
            'list' => $this->generateListWidget($data, $className),
            'card' => $this->generateCardWidget($data, $className),
            default => throw new \InvalidArgumentException("Unsupported widget type: {$widgetType}")
        };
    }

    /**
     * Get the output path for the generated file.
     *
     * @param string $name The name of the file
     * @return string The output path
     */
    public function getOutputPath(string $name): string
    {
        $basePath = $this->getBaseOutputPath();
        $widgetsPath = $this->config['output']['widgets_path'] ?? 'widgets';
        $fileName = $this->toSnakeCase($name) . $this->getFileExtension();

        return $basePath . '/' . $widgetsPath . '/' . $fileName;
    }

    /**
     * Generate form widget.
     *
     * @param array $data The model data
     * @param string $className The class name
     * @return string The form widget code
     */
    private function generateFormWidget(array $data, string $className): string
    {
        $widgetName = "{$className}Form";
        $imports = $this->getFormImports($data);
        $formFields = $this->generateFormFields($data['attributes']);
        $validation = $this->generateValidation($data);
        $submitMethod = $this->generateSubmitMethod($className);

        return $this->buildFormWidget($widgetName, $imports, $formFields, $validation, $submitMethod);
    }

    /**
     * Generate list widget.
     *
     * @param array $data The model data
     * @param string $className The class name
     * @return string The list widget code
     */
    private function generateListWidget(array $data, string $className): string
    {
        $widgetName = "{$className}List";
        $imports = $this->getListImports($data);
        $listItem = $this->generateListItem($data, $className);
        $listBuilder = $this->generateListBuilder($className);

        return $this->buildListWidget($widgetName, $imports, $listItem, $listBuilder);
    }

    /**
     * Generate card widget.
     *
     * @param array $data The model data
     * @param string $className The class name
     * @return string The card widget code
     */
    private function generateCardWidget(array $data, string $className): string
    {
        $widgetName = "{$className}Card";
        $imports = $this->getCardImports($data);
        $cardContent = $this->generateCardContent($data, $className);

        return $this->buildCardWidget($widgetName, $imports, $cardContent, $className);
    }

    /**
     * Get imports for form widget.
     *
     * @param array $data The data array
     * @return array The imports
     */
    private function getFormImports(array $data): array
    {
        $modelName = $this->toPascalCase($data['class_name']);
        $modelFileName = $this->toSnakeCase($modelName);

        return [
            "import 'package:flutter/material.dart';",
            "import '../models/{$modelFileName}.dart';",
        ];
    }

    /**
     * Get imports for list widget.
     *
     * @param array $data The data array
     * @return array The imports
     */
    private function getListImports(array $data): array
    {
        $modelName = $this->toPascalCase($data['class_name']);
        $modelFileName = $this->toSnakeCase($modelName);

        return [
            "import 'package:flutter/material.dart';",
            "import '../models/{$modelFileName}.dart';",
            "import '{$this->toSnakeCase($modelName)}_card.dart';",
        ];
    }

    /**
     * Get imports for card widget.
     *
     * @param array $data The data array
     * @return array The imports
     */
    private function getCardImports(array $data): array
    {
        $modelName = $this->toPascalCase($data['class_name']);
        $modelFileName = $this->toSnakeCase($modelName);

        return [
            "import 'package:flutter/material.dart';",
            "import '../models/{$modelFileName}.dart';",
        ];
    }

    /**
     * Generate form fields.
     *
     * @param array $attributes The model attributes
     * @return string The form fields code
     */
    private function generateFormFields(array $attributes): string
    {
        $fields = [];
        $excludedAttributes = array_merge(
            $this->config['model_analysis']['excluded_attributes'] ?? [],
            ['id', 'created_at', 'updated_at', 'deleted_at']
        );

        foreach ($attributes as $attribute) {
            if (in_array($attribute['name'], $excludedAttributes)) {
                continue;
            }

            $fieldName = $this->toCamelCase($attribute['name']);
            $labelName = Str::title(str_replace('_', ' ', $attribute['name']));
            $fieldType = $this->getFormFieldType($attribute);

            $fields[] = $this->generateFormField($fieldName, $labelName, $fieldType, $attribute);
        }

        return implode("\n", $fields);
    }

    /**
     * Generate a single form field.
     *
     * @param string $fieldName The field name
     * @param string $labelName The label name
     * @param string $fieldType The field type
     * @param array $attribute The attribute data
     * @return string The form field code
     */
    private function generateFormField(string $fieldName, string $labelName, string $fieldType, array $attribute): string
    {
        $required = !$attribute['nullable'];
        $validator = $required ? "validator: (value) => value?.isEmpty == true ? 'Please enter {$labelName}' : null," : '';

        return match ($fieldType) {
            'TextFormField' => "        TextFormField(\n" .
                              "          controller: _{$fieldName}Controller,\n" .
                              "          decoration: const InputDecoration(\n" .
                              "            labelText: '{$labelName}',\n" .
                              "            border: OutlineInputBorder(),\n" .
                              "          ),\n" .
                              "          {$validator}\n" .
                              "        ),",
            'NumberFormField' => "        TextFormField(\n" .
                                "          controller: _{$fieldName}Controller,\n" .
                                "          decoration: const InputDecoration(\n" .
                                "            labelText: '{$labelName}',\n" .
                                "            border: OutlineInputBorder(),\n" .
                                "          ),\n" .
                                "          keyboardType: TextInputType.number,\n" .
                                "          {$validator}\n" .
                                "        ),",
            'DateFormField' => "        TextFormField(\n" .
                              "          controller: _{$fieldName}Controller,\n" .
                              "          decoration: const InputDecoration(\n" .
                              "            labelText: '{$labelName}',\n" .
                              "            border: OutlineInputBorder(),\n" .
                              "            suffixIcon: Icon(Icons.calendar_today),\n" .
                              "          ),\n" .
                              "          readOnly: true,\n" .
                              "          onTap: () => _select{$this->toPascalCase($fieldName)}(),\n" .
                              "          {$validator}\n" .
                              "        ),",
            'SwitchFormField' => "        SwitchListTile(\n" .
                                "          title: const Text('{$labelName}'),\n" .
                                "          value: _{$fieldName},\n" .
                                "          onChanged: (value) => setState(() => _{$fieldName} = value),\n" .
                                "        ),",
            default => "        // TODO: Implement {$fieldType} for {$fieldName}"
        };
    }

    /**
     * Get form field type based on attribute.
     *
     * @param array $attribute The attribute data
     * @return string The form field type
     */
    private function getFormFieldType(array $attribute): string
    {
        return match ($attribute['type']) {
            'int', 'double' => 'NumberFormField',
            'bool' => 'SwitchFormField',
            'DateTime' => 'DateFormField',
            default => 'TextFormField'
        };
    }

    /**
     * Generate validation logic.
     *
     * @param array $data The model data
     * @return string The validation code
     */
    private function generateValidation(array $data): string
    {
        return "  bool _validateForm() {\n" .
               "    return _formKey.currentState?.validate() ?? false;\n" .
               "  }";
    }

    /**
     * Generate submit method.
     *
     * @param string $className The class name
     * @return string The submit method code
     */
    private function generateSubmitMethod(string $className): string
    {
        return "  void _submitForm() {\n" .
               "    if (_validateForm()) {\n" .
               "      final data = _buildFormData();\n" .
               "      widget.onSubmit?.call(data);\n" .
               "    }\n" .
               "  }\n\n" .
               "  Map<String, dynamic> _buildFormData() {\n" .
               "    // TODO: Build form data from controllers\n" .
               "    return {};\n" .
               "  }";
    }

    /**
     * Generate list item widget.
     *
     * @param array $data The model data
     * @param string $className The class name
     * @return string The list item code
     */
    private function generateListItem(array $data, string $className): string
    {
        $primaryField = $this->getPrimaryDisplayField($data['attributes']);
        $secondaryField = $this->getSecondaryDisplayField($data['attributes']);

        return "  Widget _buildListItem({$className} item) {\n" .
               "    return {$className}Card(\n" .
               "      item: item,\n" .
               "      onTap: () => widget.onItemTap?.call(item),\n" .
               "      onEdit: () => widget.onItemEdit?.call(item),\n" .
               "      onDelete: () => widget.onItemDelete?.call(item),\n" .
               "    );\n" .
               "  }";
    }

    /**
     * Generate list builder.
     *
     * @param string $className The class name
     * @return string The list builder code
     */
    private function generateListBuilder(string $className): string
    {
        return "  @override\n" .
               "  Widget build(BuildContext context) {\n" .
               "    if (widget.items.isEmpty) {\n" .
               "      return const Center(\n" .
               "        child: Text('No items found'),\n" .
               "      );\n" .
               "    }\n\n" .
               "    return ListView.builder(\n" .
               "      itemCount: widget.items.length,\n" .
               "      itemBuilder: (context, index) {\n" .
               "        return _buildListItem(widget.items[index]);\n" .
               "      },\n" .
               "    );\n" .
               "  }";
    }

    /**
     * Generate card content.
     *
     * @param array $data The model data
     * @param string $className The class name
     * @return string The card content code
     */
    private function generateCardContent(array $data, string $className): string
    {
        $primaryField = $this->getPrimaryDisplayField($data['attributes']);
        $secondaryField = $this->getSecondaryDisplayField($data['attributes']);
        $primaryFieldName = $this->toCamelCase($primaryField['name']);
        $secondaryFieldName = $secondaryField ? $this->toCamelCase($secondaryField['name']) : null;

        $subtitle = $secondaryFieldName ?
            "          subtitle: Text(widget.item.{$secondaryFieldName}?.toString() ?? ''),\n" : '';

        return "  @override\n" .
               "  Widget build(BuildContext context) {\n" .
               "    return Card(\n" .
               "      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),\n" .
               "      child: ListTile(\n" .
               "        title: Text(widget.item.{$primaryFieldName}?.toString() ?? ''),\n" .
               "{$subtitle}" .
               "        trailing: PopupMenuButton(\n" .
               "          onSelected: (value) {\n" .
               "            switch (value) {\n" .
               "              case 'edit':\n" .
               "                widget.onEdit?.call();\n" .
               "                break;\n" .
               "              case 'delete':\n" .
               "                widget.onDelete?.call();\n" .
               "                break;\n" .
               "            }\n" .
               "          },\n" .
               "          itemBuilder: (context) => [\n" .
               "            const PopupMenuItem(\n" .
               "              value: 'edit',\n" .
               "              child: Text('Edit'),\n" .
               "            ),\n" .
               "            const PopupMenuItem(\n" .
               "              value: 'delete',\n" .
               "              child: Text('Delete'),\n" .
               "            ),\n" .
               "          ],\n" .
               "        ),\n" .
               "        onTap: widget.onTap,\n" .
               "      ),\n" .
               "    );\n" .
               "  }";
    }

    /**
     * Get primary display field.
     *
     * @param array $attributes The model attributes
     * @return array The primary field
     */
    private function getPrimaryDisplayField(array $attributes): array
    {
        // Look for common primary fields
        $primaryFields = ['name', 'title', 'email', 'username'];

        foreach ($primaryFields as $field) {
            foreach ($attributes as $attribute) {
                if ($attribute['name'] === $field) {
                    return $attribute;
                }
            }
        }

        // Return first string field
        foreach ($attributes as $attribute) {
            if ($attribute['type'] === 'String' && $attribute['name'] !== 'id') {
                return $attribute;
            }
        }

        // Fallback to first field
        return $attributes[0] ?? ['name' => 'id', 'type' => 'int'];
    }

    /**
     * Get secondary display field.
     *
     * @param array $attributes The model attributes
     * @return array|null The secondary field
     */
    private function getSecondaryDisplayField(array $attributes): ?array
    {
        $primary = $this->getPrimaryDisplayField($attributes);
        $secondaryFields = ['description', 'email', 'phone', 'created_at'];

        foreach ($secondaryFields as $field) {
            foreach ($attributes as $attribute) {
                if ($attribute['name'] === $field && $attribute['name'] !== $primary['name']) {
                    return $attribute;
                }
            }
        }

        return null;
    }

    /**
     * Build form widget class.
     *
     * @param string $widgetName The widget name
     * @param array $imports The imports
     * @param string $formFields The form fields
     * @param string $validation The validation
     * @param string $submitMethod The submit method
     * @return string The complete widget code
     */
    private function buildFormWidget(
        string $widgetName,
        array $imports,
        string $formFields,
        string $validation,
        string $submitMethod
    ): string {
        $importsString = implode("\n", $imports);

        return "{$importsString}\n\n" .
               "class {$widgetName} extends StatefulWidget {\n" .
               "  final Function(Map<String, dynamic>)? onSubmit;\n" .
               "  final Map<String, dynamic>? initialData;\n\n" .
               "  const {$widgetName}({super.key, this.onSubmit, this.initialData});\n\n" .
               "  @override\n" .
               "  State<{$widgetName}> createState() => _{$widgetName}State();\n" .
               "}\n\n" .
               "class _{$widgetName}State extends State<{$widgetName}> {\n" .
               "  final _formKey = GlobalKey<FormState>();\n" .
               "  // TODO: Add controllers for form fields\n\n" .
               "  @override\n" .
               "  Widget build(BuildContext context) {\n" .
               "    return Form(\n" .
               "      key: _formKey,\n" .
               "      child: Column(\n" .
               "        children: [\n" .
               "{$formFields}\n" .
               "          const SizedBox(height: 16),\n" .
               "          ElevatedButton(\n" .
               "            onPressed: _submitForm,\n" .
               "            child: const Text('Submit'),\n" .
               "          ),\n" .
               "        ],\n" .
               "      ),\n" .
               "    );\n" .
               "  }\n\n" .
               "{$validation}\n\n" .
               "{$submitMethod}\n" .
               "}";
    }

    /**
     * Build list widget class.
     *
     * @param string $widgetName The widget name
     * @param array $imports The imports
     * @param string $listItem The list item
     * @param string $listBuilder The list builder
     * @return string The complete widget code
     */
    private function buildListWidget(
        string $widgetName,
        array $imports,
        string $listItem,
        string $listBuilder
    ): string {
        $importsString = implode("\n", $imports);
        $modelName = str_replace('List', '', $widgetName);

        return "{$importsString}\n\n" .
               "class {$widgetName} extends StatelessWidget {\n" .
               "  final List<{$modelName}> items;\n" .
               "  final Function({$modelName})? onItemTap;\n" .
               "  final Function({$modelName})? onItemEdit;\n" .
               "  final Function({$modelName})? onItemDelete;\n\n" .
               "  const {$widgetName}({\n" .
               "    super.key,\n" .
               "    required this.items,\n" .
               "    this.onItemTap,\n" .
               "    this.onItemEdit,\n" .
               "    this.onItemDelete,\n" .
               "  });\n\n" .
               "{$listBuilder}\n\n" .
               "{$listItem}\n" .
               "}";
    }

    /**
     * Build card widget class.
     *
     * @param string $widgetName The widget name
     * @param array $imports The imports
     * @param string $cardContent The card content
     * @param string $className The class name
     * @return string The complete widget code
     */
    private function buildCardWidget(
        string $widgetName,
        array $imports,
        string $cardContent,
        string $className
    ): string {
        $importsString = implode("\n", $imports);

        return "{$importsString}\n\n" .
               "class {$widgetName} extends StatelessWidget {\n" .
               "  final {$className} item;\n" .
               "  final VoidCallback? onTap;\n" .
               "  final VoidCallback? onEdit;\n" .
               "  final VoidCallback? onDelete;\n\n" .
               "  const {$widgetName}({\n" .
               "    super.key,\n" .
               "    required this.item,\n" .
               "    this.onTap,\n" .
               "    this.onEdit,\n" .
               "    this.onDelete,\n" .
               "  });\n\n" .
               "{$cardContent}\n" .
               "}";
    }
}

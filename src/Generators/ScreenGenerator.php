<?php

namespace BasharShaeb\LaravelFlutterGenerator\Generators;

use Illuminate\Support\Str;

class ScreenGenerator extends BaseGenerator
{
    /**
     * Generate screen code based on the provided data.
     *
     * @param array $data The data to generate code from
     * @param array $options Additional options for generation
     * @return string The generated screen code
     */
    public function generate(array $data, array $options = []): string
    {
        $screenType = $options['screen_type'] ?? 'list';
        $className = $this->toPascalCase($data['class_name']);

        return match ($screenType) {
            'list' => $this->generateListScreen($data, $className),
            'detail' => $this->generateDetailScreen($data, $className),
            'create' => $this->generateCreateScreen($data, $className),
            'edit' => $this->generateEditScreen($data, $className),
            default => throw new \InvalidArgumentException("Unsupported screen type: {$screenType}")
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
        $screensPath = $this->config['output']['screens_path'] ?? 'screens';
        $fileName = $this->toSnakeCase($name) . $this->getFileExtension();

        return $basePath . '/' . $screensPath . '/' . $fileName;
    }

    /**
     * Generate list screen.
     *
     * @param array $data The model data
     * @param string $className The class name
     * @return string The list screen code
     */
    private function generateListScreen(array $data, string $className): string
    {
        $screenName = "{$className}ListScreen";
        $imports = $this->getListScreenImports($data);
        $stateManagement = $this->generateStateManagement($className);
        $buildMethod = $this->generateListScreenBuild($className);
        $methods = $this->generateListScreenMethods($className);

        return $this->buildScreen($screenName, $imports, $stateManagement, $buildMethod, $methods);
    }

    /**
     * Generate detail screen.
     *
     * @param array $data The model data
     * @param string $className The class name
     * @return string The detail screen code
     */
    private function generateDetailScreen(array $data, string $className): string
    {
        $screenName = "{$className}DetailScreen";
        $imports = $this->getDetailScreenImports($data);
        $stateManagement = $this->generateDetailStateManagement($className);
        $buildMethod = $this->generateDetailScreenBuild($className, $data);
        $methods = $this->generateDetailScreenMethods($className);

        return $this->buildScreen($screenName, $imports, $stateManagement, $buildMethod, $methods);
    }

    /**
     * Generate create screen.
     *
     * @param array $data The model data
     * @param string $className The class name
     * @return string The create screen code
     */
    private function generateCreateScreen(array $data, string $className): string
    {
        $screenName = "{$className}CreateScreen";
        $imports = $this->getFormScreenImports($data);
        $stateManagement = $this->generateFormStateManagement($className);
        $buildMethod = $this->generateCreateScreenBuild($className);
        $methods = $this->generateCreateScreenMethods($className);

        return $this->buildScreen($screenName, $imports, $stateManagement, $buildMethod, $methods);
    }

    /**
     * Generate edit screen.
     *
     * @param array $data The model data
     * @param string $className The class name
     * @return string The edit screen code
     */
    private function generateEditScreen(array $data, string $className): string
    {
        $screenName = "{$className}EditScreen";
        $imports = $this->getFormScreenImports($data);
        $stateManagement = $this->generateFormStateManagement($className);
        $buildMethod = $this->generateEditScreenBuild($className);
        $methods = $this->generateEditScreenMethods($className);

        return $this->buildScreen($screenName, $imports, $stateManagement, $buildMethod, $methods);
    }

    /**
     * Get imports for list screen.
     *
     * @param array $data The data array
     * @return array The imports
     */
    private function getListScreenImports(array $data): array
    {
        $modelName = $this->toPascalCase($data['class_name']);
        $modelFileName = $this->toSnakeCase($modelName);
        $serviceFileName = $this->toSnakeCase($modelName) . '_service';

        return [
            "import 'package:flutter/material.dart';",
            "import '../models/{$modelFileName}.dart';",
            "import '../services/{$serviceFileName}.dart';",
            "import '../widgets/{$this->toSnakeCase($modelName)}_list.dart';",
            "import '{$this->toSnakeCase($modelName)}_create_screen.dart';",
            "import '{$this->toSnakeCase($modelName)}_detail_screen.dart';",
        ];
    }

    /**
     * Get imports for detail screen.
     *
     * @param array $data The data array
     * @return array The imports
     */
    private function getDetailScreenImports(array $data): array
    {
        $modelName = $this->toPascalCase($data['class_name']);
        $modelFileName = $this->toSnakeCase($modelName);
        $serviceFileName = $this->toSnakeCase($modelName) . '_service';

        return [
            "import 'package:flutter/material.dart';",
            "import '../models/{$modelFileName}.dart';",
            "import '../services/{$serviceFileName}.dart';",
            "import '{$this->toSnakeCase($modelName)}_edit_screen.dart';",
        ];
    }

    /**
     * Get imports for form screens.
     *
     * @param array $data The data array
     * @return array The imports
     */
    private function getFormScreenImports(array $data): array
    {
        $modelName = $this->toPascalCase($data['class_name']);
        $modelFileName = $this->toSnakeCase($modelName);
        $serviceFileName = $this->toSnakeCase($modelName) . '_service';

        return [
            "import 'package:flutter/material.dart';",
            "import '../models/{$modelFileName}.dart';",
            "import '../services/{$serviceFileName}.dart';",
            "import '../widgets/{$this->toSnakeCase($modelName)}_form.dart';",
        ];
    }

    /**
     * Generate state management for list screen.
     *
     * @param string $className The class name
     * @return string The state management code
     */
    private function generateStateManagement(string $className): string
    {
        return "  List<{$className}> _items = [];\n" .
               "  bool _isLoading = false;\n" .
               "  String? _error;\n" .
               "  late {$className}Service _service;\n\n" .
               "  @override\n" .
               "  void initState() {\n" .
               "    super.initState();\n" .
               "    _service = {$className}Service(ApiService(), '{$this->toSnakeCase($className)}');\n" .
               "    _loadItems();\n" .
               "  }";
    }

    /**
     * Generate state management for detail screen.
     *
     * @param string $className The class name
     * @return string The state management code
     */
    private function generateDetailStateManagement(string $className): string
    {
        return "  {$className}? _item;\n" .
               "  bool _isLoading = true;\n" .
               "  String? _error;\n" .
               "  late {$className}Service _service;\n\n" .
               "  @override\n" .
               "  void initState() {\n" .
               "    super.initState();\n" .
               "    _service = {$className}Service(ApiService(), '{$this->toSnakeCase($className)}');\n" .
               "    _loadItem();\n" .
               "  }";
    }

    /**
     * Generate state management for form screens.
     *
     * @param string $className The class name
     * @return string The state management code
     */
    private function generateFormStateManagement(string $className): string
    {
        return "  bool _isLoading = false;\n" .
               "  String? _error;\n" .
               "  late {$className}Service _service;\n\n" .
               "  @override\n" .
               "  void initState() {\n" .
               "    super.initState();\n" .
               "    _service = {$className}Service(ApiService(), '{$this->toSnakeCase($className)}');\n" .
               "  }";
    }

    /**
     * Generate build method for list screen.
     *
     * @param string $className The class name
     * @return string The build method code
     */
    private function generateListScreenBuild(string $className): string
    {
        return "  @override\n" .
               "  Widget build(BuildContext context) {\n" .
               "    return Scaffold(\n" .
               "      appBar: AppBar(\n" .
               "        title: Text('{$className} List'),\n" .
               "        actions: [\n" .
               "          IconButton(\n" .
               "            icon: const Icon(Icons.refresh),\n" .
               "            onPressed: _loadItems,\n" .
               "          ),\n" .
               "        ],\n" .
               "      ),\n" .
               "      body: _buildBody(),\n" .
               "      floatingActionButton: FloatingActionButton(\n" .
               "        onPressed: _navigateToCreate,\n" .
               "        child: const Icon(Icons.add),\n" .
               "      ),\n" .
               "    );\n" .
               "  }\n\n" .
               "  Widget _buildBody() {\n" .
               "    if (_isLoading) {\n" .
               "      return const Center(child: CircularProgressIndicator());\n" .
               "    }\n\n" .
               "    if (_error != null) {\n" .
               "      return Center(\n" .
               "        child: Column(\n" .
               "          mainAxisAlignment: MainAxisAlignment.center,\n" .
               "          children: [\n" .
               "            Text('Error: \$_error'),\n" .
               "            ElevatedButton(\n" .
               "              onPressed: _loadItems,\n" .
               "              child: const Text('Retry'),\n" .
               "            ),\n" .
               "          ],\n" .
               "        ),\n" .
               "      );\n" .
               "    }\n\n" .
               "    return {$className}List(\n" .
               "      items: _items,\n" .
               "      onItemTap: _navigateToDetail,\n" .
               "      onItemEdit: _navigateToEdit,\n" .
               "      onItemDelete: _deleteItem,\n" .
               "    );\n" .
               "  }";
    }

    /**
     * Generate build method for detail screen.
     *
     * @param string $className The class name
     * @param array $data The model data
     * @return string The build method code
     */
    private function generateDetailScreenBuild(string $className, array $data): string
    {
        $fields = $this->generateDetailFields($data['attributes']);

        return "  @override\n" .
               "  Widget build(BuildContext context) {\n" .
               "    return Scaffold(\n" .
               "      appBar: AppBar(\n" .
               "        title: Text('{$className} Details'),\n" .
               "        actions: [\n" .
               "          if (_item != null)\n" .
               "            IconButton(\n" .
               "              icon: const Icon(Icons.edit),\n" .
               "              onPressed: _navigateToEdit,\n" .
               "            ),\n" .
               "        ],\n" .
               "      ),\n" .
               "      body: _buildBody(),\n" .
               "    );\n" .
               "  }\n\n" .
               "  Widget _buildBody() {\n" .
               "    if (_isLoading) {\n" .
               "      return const Center(child: CircularProgressIndicator());\n" .
               "    }\n\n" .
               "    if (_error != null) {\n" .
               "      return Center(\n" .
               "        child: Column(\n" .
               "          mainAxisAlignment: MainAxisAlignment.center,\n" .
               "          children: [\n" .
               "            Text('Error: \$_error'),\n" .
               "            ElevatedButton(\n" .
               "              onPressed: _loadItem,\n" .
               "              child: const Text('Retry'),\n" .
               "            ),\n" .
               "          ],\n" .
               "        ),\n" .
               "      );\n" .
               "    }\n\n" .
               "    if (_item == null) {\n" .
               "      return const Center(child: Text('Item not found'));\n" .
               "    }\n\n" .
               "    return SingleChildScrollView(\n" .
               "      padding: const EdgeInsets.all(16),\n" .
               "      child: Column(\n" .
               "        crossAxisAlignment: CrossAxisAlignment.start,\n" .
               "        children: [\n" .
               "{$fields}\n" .
               "        ],\n" .
               "      ),\n" .
               "    );\n" .
               "  }";
    }

    /**
     * Generate detail fields.
     *
     * @param array $attributes The model attributes
     * @return string The detail fields code
     */
    private function generateDetailFields(array $attributes): string
    {
        $fields = [];
        $excludedAttributes = $this->config['model_analysis']['excluded_attributes'] ?? [];

        foreach ($attributes as $attribute) {
            if (in_array($attribute['name'], $excludedAttributes)) {
                continue;
            }

            $fieldName = $this->toCamelCase($attribute['name']);
            $labelName = Str::title(str_replace('_', ' ', $attribute['name']));

            $fields[] = "          _buildDetailField('{$labelName}', _item!.{$fieldName}?.toString() ?? 'N/A'),";
        }

        return implode("\n", $fields);
    }

    /**
     * Generate build method for create screen.
     *
     * @param string $className The class name
     * @return string The build method code
     */
    private function generateCreateScreenBuild(string $className): string
    {
        return "  @override\n" .
               "  Widget build(BuildContext context) {\n" .
               "    return Scaffold(\n" .
               "      appBar: AppBar(\n" .
               "        title: const Text('Create {$className}'),\n" .
               "      ),\n" .
               "      body: _isLoading\n" .
               "          ? const Center(child: CircularProgressIndicator())\n" .
               "          : SingleChildScrollView(\n" .
               "              padding: const EdgeInsets.all(16),\n" .
               "              child: {$className}Form(\n" .
               "                onSubmit: _createItem,\n" .
               "              ),\n" .
               "            ),\n" .
               "    );\n" .
               "  }";
    }

    /**
     * Generate build method for edit screen.
     *
     * @param string $className The class name
     * @return string The build method code
     */
    private function generateEditScreenBuild(string $className): string
    {
        return "  @override\n" .
               "  Widget build(BuildContext context) {\n" .
               "    return Scaffold(\n" .
               "      appBar: AppBar(\n" .
               "        title: const Text('Edit {$className}'),\n" .
               "      ),\n" .
               "      body: _isLoading\n" .
               "          ? const Center(child: CircularProgressIndicator())\n" .
               "          : SingleChildScrollView(\n" .
               "              padding: const EdgeInsets.all(16),\n" .
               "              child: {$className}Form(\n" .
               "                initialData: widget.item.toJson(),\n" .
               "                onSubmit: _updateItem,\n" .
               "              ),\n" .
               "            ),\n" .
               "    );\n" .
               "  }";
    }

    /**
     * Generate methods for list screen.
     *
     * @param string $className The class name
     * @return string The methods code
     */
    private function generateListScreenMethods(string $className): string
    {
        return "  Future<void> _loadItems() async {\n" .
               "    setState(() {\n" .
               "      _isLoading = true;\n" .
               "      _error = null;\n" .
               "    });\n\n" .
               "    try {\n" .
               "      final items = await _service.getAll();\n" .
               "      setState(() {\n" .
               "        _items = items;\n" .
               "        _isLoading = false;\n" .
               "      });\n" .
               "    } catch (e) {\n" .
               "      setState(() {\n" .
               "        _error = e.toString();\n" .
               "        _isLoading = false;\n" .
               "      });\n" .
               "    }\n" .
               "  }\n\n" .
               "  void _navigateToCreate() async {\n" .
               "    final result = await Navigator.push(\n" .
               "      context,\n" .
               "      MaterialPageRoute(\n" .
               "        builder: (context) => const {$className}CreateScreen(),\n" .
               "      ),\n" .
               "    );\n\n" .
               "    if (result == true) {\n" .
               "      _loadItems();\n" .
               "    }\n" .
               "  }\n\n" .
               "  void _navigateToDetail({$className} item) {\n" .
               "    Navigator.push(\n" .
               "      context,\n" .
               "      MaterialPageRoute(\n" .
               "        builder: (context) => {$className}DetailScreen(itemId: item.id),\n" .
               "      ),\n" .
               "    );\n" .
               "  }\n\n" .
               "  void _navigateToEdit({$className} item) async {\n" .
               "    final result = await Navigator.push(\n" .
               "      context,\n" .
               "      MaterialPageRoute(\n" .
               "        builder: (context) => {$className}EditScreen(item: item),\n" .
               "      ),\n" .
               "    );\n\n" .
               "    if (result == true) {\n" .
               "      _loadItems();\n" .
               "    }\n" .
               "  }\n\n" .
               "  void _deleteItem({$className} item) async {\n" .
               "    final confirmed = await showDialog<bool>(\n" .
               "      context: context,\n" .
               "      builder: (context) => AlertDialog(\n" .
               "        title: const Text('Confirm Delete'),\n" .
               "        content: const Text('Are you sure you want to delete this item?'),\n" .
               "        actions: [\n" .
               "          TextButton(\n" .
               "            onPressed: () => Navigator.pop(context, false),\n" .
               "            child: const Text('Cancel'),\n" .
               "          ),\n" .
               "          TextButton(\n" .
               "            onPressed: () => Navigator.pop(context, true),\n" .
               "            child: const Text('Delete'),\n" .
               "          ),\n" .
               "        ],\n" .
               "      ),\n" .
               "    );\n\n" .
               "    if (confirmed == true) {\n" .
               "      try {\n" .
               "        await _service.delete(item.id);\n" .
               "        _loadItems();\n" .
               "      } catch (e) {\n" .
               "        ScaffoldMessenger.of(context).showSnackBar(\n" .
               "          SnackBar(content: Text('Failed to delete: \$e')),\n" .
               "        );\n" .
               "      }\n" .
               "    }\n" .
               "  }";
    }

    /**
     * Generate methods for detail screen.
     *
     * @param string $className The class name
     * @return string The methods code
     */
    private function generateDetailScreenMethods(string $className): string
    {
        return "  Future<void> _loadItem() async {\n" .
               "    setState(() {\n" .
               "      _isLoading = true;\n" .
               "      _error = null;\n" .
               "    });\n\n" .
               "    try {\n" .
               "      final item = await _service.getById(widget.itemId);\n" .
               "      setState(() {\n" .
               "        _item = item;\n" .
               "        _isLoading = false;\n" .
               "      });\n" .
               "    } catch (e) {\n" .
               "      setState(() {\n" .
               "        _error = e.toString();\n" .
               "        _isLoading = false;\n" .
               "      });\n" .
               "    }\n" .
               "  }\n\n" .
               "  void _navigateToEdit() async {\n" .
               "    if (_item == null) return;\n\n" .
               "    final result = await Navigator.push(\n" .
               "      context,\n" .
               "      MaterialPageRoute(\n" .
               "        builder: (context) => {$className}EditScreen(item: _item!),\n" .
               "      ),\n" .
               "    );\n\n" .
               "    if (result == true) {\n" .
               "      _loadItem();\n" .
               "    }\n" .
               "  }\n\n" .
               "  Widget _buildDetailField(String label, String value) {\n" .
               "    return Padding(\n" .
               "      padding: const EdgeInsets.only(bottom: 16),\n" .
               "      child: Column(\n" .
               "        crossAxisAlignment: CrossAxisAlignment.start,\n" .
               "        children: [\n" .
               "          Text(\n" .
               "            label,\n" .
               "            style: Theme.of(context).textTheme.labelLarge,\n" .
               "          ),\n" .
               "          const SizedBox(height: 4),\n" .
               "          Text(\n" .
               "            value,\n" .
               "            style: Theme.of(context).textTheme.bodyLarge,\n" .
               "          ),\n" .
               "        ],\n" .
               "      ),\n" .
               "    );\n" .
               "  }";
    }

    /**
     * Generate methods for create screen.
     *
     * @param string $className The class name
     * @return string The methods code
     */
    private function generateCreateScreenMethods(string $className): string
    {
        return "  Future<void> _createItem(Map<String, dynamic> data) async {\n" .
               "    setState(() => _isLoading = true);\n\n" .
               "    try {\n" .
               "      await _service.create(data);\n" .
               "      Navigator.pop(context, true);\n" .
               "    } catch (e) {\n" .
               "      setState(() => _isLoading = false);\n" .
               "      ScaffoldMessenger.of(context).showSnackBar(\n" .
               "        SnackBar(content: Text('Failed to create: \$e')),\n" .
               "      );\n" .
               "    }\n" .
               "  }";
    }

    /**
     * Generate methods for edit screen.
     *
     * @param string $className The class name
     * @return string The methods code
     */
    private function generateEditScreenMethods(string $className): string
    {
        return "  Future<void> _updateItem(Map<String, dynamic> data) async {\n" .
               "    setState(() => _isLoading = true);\n\n" .
               "    try {\n" .
               "      await _service.update(widget.item.id, data);\n" .
               "      Navigator.pop(context, true);\n" .
               "    } catch (e) {\n" .
               "      setState(() => _isLoading = false);\n" .
               "      ScaffoldMessenger.of(context).showSnackBar(\n" .
               "        SnackBar(content: Text('Failed to update: \$e')),\n" .
               "      );\n" .
               "    }\n" .
               "  }";
    }

    /**
     * Build screen class.
     *
     * @param string $screenName The screen name
     * @param array $imports The imports
     * @param string $stateManagement The state management
     * @param string $buildMethod The build method
     * @param string $methods The methods
     * @return string The complete screen code
     */
    private function buildScreen(
        string $screenName,
        array $imports,
        string $stateManagement,
        string $buildMethod,
        string $methods
    ): string {
        $importsString = implode("\n", $imports);
        $constructorParams = $this->getConstructorParams($screenName);

        return "{$importsString}\n\n" .
               "class {$screenName} extends StatefulWidget {\n" .
               "{$constructorParams}\n\n" .
               "  const {$screenName}({super.key{$this->getConstructorParamsString($screenName)}});\n\n" .
               "  @override\n" .
               "  State<{$screenName}> createState() => _{$screenName}State();\n" .
               "}\n\n" .
               "class _{$screenName}State extends State<{$screenName}> {\n" .
               "{$stateManagement}\n\n" .
               "{$buildMethod}\n\n" .
               "{$methods}\n" .
               "}";
    }

    /**
     * Get constructor parameters for screen.
     *
     * @param string $screenName The screen name
     * @return string The constructor parameters
     */
    private function getConstructorParams(string $screenName): string
    {
        if (Str::contains($screenName, 'Detail')) {
            return "  final int itemId;";
        }

        if (Str::contains($screenName, 'Edit')) {
            $modelName = str_replace(['EditScreen', 'Edit'], '', $screenName);
            return "  final {$modelName} item;";
        }

        return "";
    }

    /**
     * Get constructor parameters string.
     *
     * @param string $screenName The screen name
     * @return string The constructor parameters string
     */
    private function getConstructorParamsString(string $screenName): string
    {
        if (Str::contains($screenName, 'Detail')) {
            return ", required this.itemId";
        }

        if (Str::contains($screenName, 'Edit')) {
            return ", required this.item";
        }

        return "";
    }
}

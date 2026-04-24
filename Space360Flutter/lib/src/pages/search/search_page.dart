import 'dart:async';
import 'package:flutter/material.dart';
import 'package:space360_flutter/src/models/property_model.dart';
import 'package:space360_flutter/src/services/explore_service.dart';
import 'package:space360_flutter/src/utils/resource.dart';
import 'package:space360_flutter/src/widgets/empty_state.dart';
import 'package:space360_flutter/src/widgets/property_card.dart';

const _kGold = Color(0xFFD4A843);
const _kBg = Color(0xFF0D0D0D);
const _kSurface = Color(0xFF1A1A1A);
const _kText = Color(0xFFF0F0F0);
const _kSubtext = Color(0xFF9E9E9E);

class SearchPage extends StatefulWidget {
  const SearchPage({super.key});

  @override
  State<SearchPage> createState() => _SearchPageState();
}

class _SearchPageState extends State<SearchPage> {
  final _service = ExploreService();
  final _controller = TextEditingController();
  Timer? _debounce;
  Resource<List<PropertyModel>>? _state;
  bool _searching = false;

  @override
  void dispose() {
    _debounce?.cancel();
    _controller.dispose();
    super.dispose();
  }

  void _onChanged(String value) {
    _debounce?.cancel();
    if (value.trim().isEmpty) {
      setState(() => _state = null);
      return;
    }
    _debounce = Timer(const Duration(milliseconds: 500), () => _search(value.trim()));
  }

  Future<void> _search(String q) async {
    setState(() => _searching = true);
    final result = await _service.search(q: q);
    if (mounted) setState(() { _state = result; _searching = false; });
  }

  void _clear() {
    _controller.clear();
    setState(() => _state = null);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _kBg,
      appBar: AppBar(
        backgroundColor: _kBg,
        elevation: 0,
        title: const Text('Buscar', style: TextStyle(color: _kText, fontWeight: FontWeight.bold)),
        centerTitle: false,
      ),
      body: Column(
        children: [
          _SearchBar(controller: _controller, onChanged: _onChanged, onClear: _clear),
          Expanded(child: _buildResults()),
        ],
      ),
    );
  }

  Widget _buildResults() {
    if (_searching) {
      return const Center(
        child: CircularProgressIndicator(valueColor: AlwaysStoppedAnimation<Color>(_kGold)),
      );
    }

    if (_state == null) {
      return const EmptyState(
        message: 'Escribe para buscar propiedades, vehículos y más',
        icon: Icons.search_rounded,
      );
    }

    return switch (_state!) {
      AppError(:final message) => ErrorState(
          message: message,
          onRetry: () => _search(_controller.text.trim()),
        ),
      Success(:final data) when data.isEmpty => EmptyState(
          message: 'Sin resultados para "${_controller.text}"',
          icon: Icons.find_in_page_outlined,
          actionLabel: 'Limpiar búsqueda',
          onAction: _clear,
        ),
      Success(:final data) => GridView.builder(
          padding: const EdgeInsets.all(16),
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 2,
            crossAxisSpacing: 12,
            mainAxisSpacing: 12,
            childAspectRatio: 0.75,
          ),
          itemCount: data.length,
          itemBuilder: (context, i) => PropertyCard(property: data[i]),
        ),
      _ => const SizedBox(),
    };
  }
}

class _SearchBar extends StatelessWidget {
  final TextEditingController controller;
  final ValueChanged<String> onChanged;
  final VoidCallback onClear;

  const _SearchBar({required this.controller, required this.onChanged, required this.onClear});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 4, 16, 12),
      child: TextField(
        controller: controller,
        onChanged: onChanged,
        autofocus: false,
        style: const TextStyle(color: _kText),
        cursorColor: _kGold,
        decoration: InputDecoration(
          hintText: 'Nombre, código, ubicación...',
          hintStyle: const TextStyle(color: _kSubtext),
          prefixIcon: const Icon(Icons.search_rounded, color: _kSubtext),
          suffixIcon: controller.text.isNotEmpty
              ? IconButton(
                  icon: const Icon(Icons.clear_rounded, color: _kSubtext),
                  onPressed: onClear,
                )
              : null,
          filled: true,
          fillColor: _kSurface,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: BorderSide.none,
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: _kGold, width: 1.5),
          ),
          contentPadding: const EdgeInsets.symmetric(vertical: 14),
        ),
      ),
    );
  }
}

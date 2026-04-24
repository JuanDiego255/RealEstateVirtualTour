import 'package:flutter/material.dart';
import 'package:space360_flutter/src/models/property_model.dart';
import 'package:space360_flutter/src/services/explore_service.dart';
import 'package:space360_flutter/src/utils/resource.dart';
import 'package:space360_flutter/src/widgets/empty_state.dart';
import 'package:space360_flutter/src/widgets/property_card.dart';

const _kGold = Color(0xFFD4A843);
const _kBg = Color(0xFF0D0D0D);
const _kText = Color(0xFFF0F0F0);

class PublicationsPage extends StatefulWidget {
  final String categorySlug;
  final String subcategorySlug;
  final String title;

  const PublicationsPage({
    super.key,
    required this.categorySlug,
    required this.subcategorySlug,
    required this.title,
  });

  @override
  State<PublicationsPage> createState() => _PublicationsPageState();
}

class _PublicationsPageState extends State<PublicationsPage> {
  final _service = ExploreService();
  Resource<List<PropertyModel>>? _state;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _state = null);
    final result = await _service.getPublications(widget.categorySlug, widget.subcategorySlug);
    if (!mounted) return;

    setState(() {
      _state = switch (result) {
        AppError(:final message) => AppError(message),
        Success(:final data) => Success(
            (data['publications'] as List<dynamic>)
                .map((e) => PropertyModel.fromJson(e as Map<String, dynamic>))
                .toList(),
          ),
        _ => AppError('Error desconocido'),
      };
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _kBg,
      appBar: AppBar(
        backgroundColor: _kBg,
        elevation: 0,
        leading: const BackButton(color: _kText),
        title: Text(widget.title, style: const TextStyle(color: _kText, fontWeight: FontWeight.bold)),
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_state == null) {
      return const Center(
        child: CircularProgressIndicator(valueColor: AlwaysStoppedAnimation<Color>(_kGold)),
      );
    }

    return switch (_state!) {
      AppError(:final message) => ErrorState(message: message, onRetry: _load),
      Success(:final data) when data.isEmpty =>
        const EmptyState(message: 'No hay publicaciones disponibles', icon: Icons.home_work_outlined),
      Success(:final data) => RefreshIndicator(
          color: _kGold,
          backgroundColor: const Color(0xFF1A1A1A),
          onRefresh: _load,
          child: GridView.builder(
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
        ),
      _ => const SizedBox(),
    };
  }
}

import 'package:flutter/material.dart';
import 'package:space360_flutter/src/models/category_model.dart';
import 'package:space360_flutter/src/models/sector_model.dart';
import 'package:space360_flutter/src/pages/explore/category_detail_page.dart';
import 'package:space360_flutter/src/services/explore_service.dart';
import 'package:space360_flutter/src/utils/resource.dart';
import 'package:space360_flutter/src/widgets/category_card.dart';
import 'package:space360_flutter/src/widgets/empty_state.dart';

const _kGold = Color(0xFFD4A843);
const _kBg = Color(0xFF0D0D0D);
const _kText = Color(0xFFF0F0F0);
const _kSubtext = Color(0xFF9E9E9E);

class SectorDetailPage extends StatefulWidget {
  final SectorModel sector;

  const SectorDetailPage({super.key, required this.sector});

  @override
  State<SectorDetailPage> createState() => _SectorDetailPageState();
}

class _SectorDetailPageState extends State<SectorDetailPage> {
  final _service = ExploreService();
  Resource<SectorModel>? _state;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _state = null);
    final result = await _service.getSector(widget.sector.slug);
    if (mounted) setState(() => _state = result);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _kBg,
      appBar: AppBar(
        backgroundColor: _kBg,
        elevation: 0,
        leading: const BackButton(color: _kText),
        title: Text(
          widget.sector.name,
          style: const TextStyle(color: _kText, fontWeight: FontWeight.bold),
        ),
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
      Success(:final data) when data.categories.isEmpty =>
        const EmptyState(message: 'No hay sucursales disponibles', icon: Icons.store_outlined),
      Success(:final data) => RefreshIndicator(
          color: _kGold,
          backgroundColor: const Color(0xFF1A1A1A),
          onRefresh: _load,
          child: ListView(
            padding: const EdgeInsets.all(16),
            children: [
              if (data.description != null) ...[
                Text(data.description!, style: const TextStyle(color: _kSubtext, fontSize: 13)),
                const SizedBox(height: 16),
              ],
              ...data.categories.map(
                (cat) => Padding(
                  padding: const EdgeInsets.only(bottom: 12),
                  child: CategoryCard(
                    category: cat,
                    onTap: () => Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => CategoryDetailPage(categorySlug: cat.slug),
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      _ => const SizedBox(),
    };
  }
}

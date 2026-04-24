import 'package:flutter/material.dart';
import 'package:space360_flutter/src/models/sector_model.dart';
import 'package:space360_flutter/src/pages/explore/sector_detail_page.dart';
import 'package:space360_flutter/src/services/explore_service.dart';
import 'package:space360_flutter/src/utils/resource.dart';
import 'package:space360_flutter/src/widgets/empty_state.dart';
import 'package:space360_flutter/src/widgets/sector_card.dart';

const _kGold = Color(0xFFD4A843);
const _kBg = Color(0xFF0D0D0D);
const _kText = Color(0xFFF0F0F0);

class SectorsPage extends StatefulWidget {
  const SectorsPage({super.key});

  @override
  State<SectorsPage> createState() => _SectorsPageState();
}

class _SectorsPageState extends State<SectorsPage> {
  final _service = ExploreService();
  Resource<List<SectorModel>>? _state;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _state = null);
    final result = await _service.getSectors();
    if (mounted) setState(() => _state = result);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _kBg,
      appBar: AppBar(
        backgroundColor: _kBg,
        elevation: 0,
        title: const Text('Explorar', style: TextStyle(color: _kText, fontWeight: FontWeight.bold)),
        centerTitle: false,
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_state == null) {
      return const Center(
        child: CircularProgressIndicator(
          valueColor: AlwaysStoppedAnimation<Color>(_kGold),
        ),
      );
    }

    return switch (_state!) {
      AppError(:final message) => ErrorState(message: message, onRetry: _load),
      Success(:final data) when data.isEmpty =>
        const EmptyState(message: 'No hay sectores disponibles', icon: Icons.category_outlined),
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
              childAspectRatio: 1.0,
            ),
            itemCount: data.length,
            itemBuilder: (context, i) => SectorCard(
              sector: data[i],
              onTap: () => Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => SectorDetailPage(sector: data[i]),
                ),
              ),
            ),
          ),
        ),
      _ => const SizedBox(),
    };
  }
}

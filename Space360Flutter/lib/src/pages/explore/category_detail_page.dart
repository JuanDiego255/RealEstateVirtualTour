import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:space360_flutter/src/models/category_model.dart';
import 'package:space360_flutter/src/pages/explore/publications_page.dart';
import 'package:space360_flutter/src/services/explore_service.dart';
import 'package:space360_flutter/src/utils/resource.dart';
import 'package:space360_flutter/src/widgets/empty_state.dart';

const _kGold = Color(0xFFD4A843);
const _kBg = Color(0xFF0D0D0D);
const _kSurface = Color(0xFF1A1A1A);
const _kText = Color(0xFFF0F0F0);
const _kSubtext = Color(0xFF9E9E9E);

class CategoryDetailPage extends StatefulWidget {
  final String categorySlug;

  const CategoryDetailPage({super.key, required this.categorySlug});

  @override
  State<CategoryDetailPage> createState() => _CategoryDetailPageState();
}

class _CategoryDetailPageState extends State<CategoryDetailPage> {
  final _service = ExploreService();
  Resource<CategoryModel>? _state;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _state = null);
    final result = await _service.getCategory(widget.categorySlug);
    if (mounted) setState(() => _state = result);
  }

  @override
  Widget build(BuildContext context) {
    final cat = (_state is Success<CategoryModel>) ? (_state as Success<CategoryModel>).data : null;

    return Scaffold(
      backgroundColor: _kBg,
      appBar: AppBar(
        backgroundColor: _kBg,
        elevation: 0,
        leading: const BackButton(color: _kText),
        title: Text(
          cat?.name ?? 'Sucursal',
          style: const TextStyle(color: _kText, fontWeight: FontWeight.bold),
        ),
        actions: [
          if (cat?.whatsappUrl != null)
            IconButton(
              icon: const Icon(Icons.chat_rounded, color: Color(0xFF25D366)),
              onPressed: () => _launchUrl(cat!.whatsappUrl!),
              tooltip: 'WhatsApp',
            ),
          if (cat?.contactPhone != null)
            IconButton(
              icon: const Icon(Icons.phone_rounded, color: _kGold),
              onPressed: () => _launchUrl('tel:${cat!.contactPhone}'),
              tooltip: 'Llamar',
            ),
        ],
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
      Success(:final data) => RefreshIndicator(
          color: _kGold,
          backgroundColor: const Color(0xFF1A1A1A),
          onRefresh: _load,
          child: ListView(
            padding: EdgeInsets.zero,
            children: [
              _Header(category: data),
              if (data.subcategories.isEmpty)
                const Padding(
                  padding: EdgeInsets.only(top: 40),
                  child: EmptyState(message: 'No hay categorías disponibles', icon: Icons.folder_open_outlined),
                )
              else
                Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Categorías',
                        style: TextStyle(color: _kText, fontSize: 16, fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 12),
                      ...data.subcategories.map(
                        (sub) => _SubcategoryTile(
                          name: sub.name,
                          propertiesCount: sub.propertiesCount,
                          imageUrl: sub.image,
                          onTap: () => Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (_) => PublicationsPage(
                                categorySlug: data.slug,
                                subcategorySlug: sub.slug,
                                title: sub.name,
                              ),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
            ],
          ),
        ),
      _ => const SizedBox(),
    };
  }

  Future<void> _launchUrl(String url) async {
    final uri = Uri.parse(url);
    if (await canLaunchUrl(uri)) await launchUrl(uri);
  }
}

class _Header extends StatelessWidget {
  final CategoryModel category;
  const _Header({required this.category});

  @override
  Widget build(BuildContext context) {
    final cover = category.coverImage ?? category.logo;
    return Column(
      children: [
        if (cover != null)
          SizedBox(
            height: 180,
            width: double.infinity,
            child: Image.network(
              cover,
              fit: BoxFit.cover,
              errorBuilder: (_, __, ___) => Container(color: _kSurface),
            ),
          ),
        Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              if (category.description != null)
                Text(category.description!, style: const TextStyle(color: _kSubtext, fontSize: 13)),
              if (category.address != null) ...[
                const SizedBox(height: 8),
                Row(
                  children: [
                    const Icon(Icons.location_on_outlined, size: 14, color: _kSubtext),
                    const SizedBox(width: 4),
                    Expanded(
                      child: Text(
                        category.address!,
                        style: const TextStyle(color: _kSubtext, fontSize: 13),
                      ),
                    ),
                  ],
                ),
              ],
              if (category.contactPhone != null) ...[
                const SizedBox(height: 6),
                Row(
                  children: [
                    const Icon(Icons.phone_outlined, size: 14, color: _kSubtext),
                    const SizedBox(width: 4),
                    Text(category.contactPhone!, style: const TextStyle(color: _kSubtext, fontSize: 13)),
                  ],
                ),
              ],
            ],
          ),
        ),
        const Divider(color: Color(0xFF2A2A2A), height: 1),
      ],
    );
  }
}

class _SubcategoryTile extends StatelessWidget {
  final String name;
  final int propertiesCount;
  final String? imageUrl;
  final VoidCallback onTap;

  const _SubcategoryTile({
    required this.name,
    required this.propertiesCount,
    this.imageUrl,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: _kSurface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.white.withOpacity(0.06)),
      ),
      child: ListTile(
        onTap: onTap,
        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
        leading: imageUrl != null
            ? ClipRRect(
                borderRadius: BorderRadius.circular(8),
                child: Image.network(
                  imageUrl!,
                  width: 48,
                  height: 48,
                  fit: BoxFit.cover,
                  errorBuilder: (_, __, ___) => _iconBox(),
                ),
              )
            : _iconBox(),
        title: Text(name, style: const TextStyle(color: _kText, fontSize: 14, fontWeight: FontWeight.w600)),
        subtitle: Text(
          '$propertiesCount publicación${propertiesCount == 1 ? '' : 'es'}',
          style: const TextStyle(color: _kGold, fontSize: 12),
        ),
        trailing: const Icon(Icons.chevron_right_rounded, color: _kSubtext),
      ),
    );
  }

  Widget _iconBox() => Container(
        width: 48,
        height: 48,
        decoration: BoxDecoration(color: const Color(0xFF222222), borderRadius: BorderRadius.circular(8)),
        child: const Icon(Icons.folder_rounded, color: Color(0xFF444444), size: 24),
      );
}

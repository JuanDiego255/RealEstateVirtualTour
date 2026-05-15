<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  body { font-family: helvetica, sans-serif; font-size: 12px; color: #333; margin: 20px; }
  h1 { color: #1a1a2e; font-size: 20px; border-bottom: 3px solid #c2ac1f; padding-bottom: 8px; margin-bottom: 4px; }
  .manual-subtitle { color: #666; font-size: 11px; margin-bottom: 18px; }
  h2 { color: #1a1a2e; font-size: 15px; margin-top: 24px; border-left: 4px solid #c2ac1f; padding-left: 10px; }
  h3 { color: #444; font-size: 13px; margin-top: 16px; }
  p, li { font-size: 12px; line-height: 1.6; color: #333; }
  ul, ol { padding-left: 20px; }
  table { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 11px; }
  th { background: #1a1a2e; color: #fff; padding: 6px 8px; text-align: left; }
  td { border: 1px solid #ddd; padding: 5px 8px; }
  tr:nth-child(even) { background: #f9f9f9; }
  .badge { display: inline-block; padding: 2px 7px; border-radius: 10px; font-size: 10px; font-weight: bold; }
  .page-break { page-break-after: always; }
  .manual-tip { background: #fffbeb; border-left: 4px solid #c2ac1f; padding: 8px 10px; margin: 10px 0; font-size: 11px; }
  .manual-note { background: #eff6ff; border-left: 4px solid #3b82f6; padding: 8px 10px; margin: 10px 0; font-size: 11px; }
  .manual-warn { background: #fff1f2; border-left: 4px solid #ef4444; padding: 8px 10px; margin: 10px 0; font-size: 11px; }
  .step-list { list-style: decimal; padding-left: 20px; }
  .step-list li { margin-bottom: 4px; }
  .toc-list { column-count: 2; font-size: 11px; padding-left: 18px; }
  .toc-list li { margin-bottom: 3px; }
  .section-divider { border: none; border-top: 1px solid #e5e7eb; margin: 20px 0; }
  pre { background: #f3f4f6; padding: 6px 10px; border-radius: 4px; font-size: 10px; }
</style>
</head>
<body>

@include('admin.crm.manual._sections', ['isPdf' => true])

</body>
</html>

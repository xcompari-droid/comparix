import React from "react";

export type Metric = { key: string; label: string; better: "higher" | "lower"; unit?: string };
export type Item = {
  id: string | number;
  name: string;
  slug?: string;
  image?: string | null;
  price?: number | null;
  specs: Record<string, any>;
  metrics: Record<string, number | string | null>;
};

function parseNum(v: any): number | null {
  if (v == null) return null;
  if (typeof v === "number" && isFinite(v)) return v;
  if (typeof v === "string") {
    const n = parseFloat(v.replace(/[^0-9.\-]/g, ""));
    return isNaN(n) ? null : n;
  }
  return null;
}

function normalize(values: (number | null)[], better: "higher" | "lower"): number[] {
  const nums = values.filter((v): v is number => v != null);
  if (nums.length === 0) return values.map(() => 0);
  const min = Math.min(...nums); const max = Math.max(...nums); const span = max - min || 1;
  return values.map(v => {
    if (v == null) return 0;
    const pct = (v - min) / span;
    return Math.round((better === "higher" ? pct : 1 - pct) * 100);
  });
}

const badge = "inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-medium";

export default function MobileLaptopCompare({ items, metrics, title = "Comparație" }: { items: Item[]; metrics: Metric[]; title?: string; }) {
  const rows = metrics.map(m => {
    const vals = items.map(it => parseNum(it.metrics[m.key]));
    const norm = normalize(vals, m.better);
    return { metric: m, raw: vals, norm };
  });

  return (
    <div className="bg-white">
      <div className="sticky top-0 z-30 border-b bg-white/90 backdrop-blur supports-[backdrop-filter]:bg-white/60">
        <div className="flex items-center justify-between px-3 py-2">
          <div className="text-sm"><span className="font-semibold">{items.length}</span> produse • <span className="font-medium">{title}</span></div>
          <a href="#specs" className="rounded-lg bg-black px-3 py-1.5 text-white text-sm">Specificații</a>
        </div>
      </div>
      <div className="snap-x snap-mandatory overflow-x-auto px-3 py-3 flex gap-3">
        {items.map((it) => (
          <div key={it.id} className="snap-start shrink-0 w-[78%] max-w-xs border rounded-2xl shadow-sm bg-white">
            {it.image ? (
              <img src={it.image} alt={it.name} className="w-full aspect-[4/3] object-cover rounded-t-2xl"/>
            ) : (
              <div className="w-full aspect-[4/3] rounded-t-2xl bg-slate-100"/>
            )}
            <div className="p-3">
              <div className="text-[11px] text-slate-500">Laptop</div>
              <div className="font-semibold leading-snug">{it.name}</div>
              <div className="mt-1 flex flex-wrap gap-1">
                {it.specs?.cpu_model && <span className={`${badge}`}>{String(it.specs.cpu_model).split(" ").slice(0,2).join(" ")}</span>}
                {it.specs?.gpu_model && <span className={`${badge}`}>{String(it.specs.gpu_model).replace(/NVIDIA |GeForce /g, "")}</span>}
                {it.specs?.ram_gb && <span className={`${badge}`}>{it.specs.ram_gb}GB RAM</span>}
                {it.specs?.storage_gb && <span className={`${badge}`}>{it.specs.storage_gb}GB</span>}
              </div>
              <div className="mt-2 flex items-center justify-between">
                <div className="text-sm font-medium">{it.price ? `${it.price} RON` : ""}</div>
                {it.slug && <a href={`/product/${it.slug}`} className="text-sm text-blue-600">Detalii</a>}
              </div>
            </div>
          </div>
        ))}
      </div>
      <div className="px-3 space-y-2">
        <div className="text-sm font-medium">Scoruri pe criterii</div>
        {rows.map((r, idx) => (
          <div key={idx} className="rounded-xl border p-3">
            <div className="mb-2 text-[13px] text-slate-600">{r.metric.label}{r.metric.unit ? ` (${r.metric.unit})` : ''}</div>
            <div className="space-y-2">
              {items.map((it, i) => (
                <div key={it.id} className="flex items-center gap-3">
                  <div className="w-24 text-[12px] leading-tight truncate">{it.name}</div>
                  <div className="flex-1 h-2 rounded-full bg-slate-100 overflow-hidden">
                    <div className="h-full rounded-full bg-black" style={{ width: `${r.norm[i]}%` }} />
                  </div>
                  <div className="w-10 text-right text-[12px] font-semibold">{r.norm[i]}</div>
                </div>
              ))}
            </div>
          </div>
        ))}
      </div>
      <div id="specs" className="px-3 py-4">
        <div className="text-sm font-medium mb-2">Specificații principale</div>
        <div className="space-y-4">
          {[
            {k:'cpu_model', l:'CPU'},
            {k:'gpu_model', l:'GPU'},
            {k:'display_size_in', l:'Ecran (inch)'},
            {k:'display_brightness_nits', l:'Luminozitate (nits)'},
            {k:'ram_gb', l:'RAM (GB)'},
            {k:'storage_gb', l:'SSD (GB)'},
            {k:'battery_wh', l:'Baterie (Wh)'},
            {k:'weight_kg', l:'Greutate (kg)'}
          ].map(row => {
            // Gather values and normalize for bar length
            const vals = items.map(it => parseNum(it.specs?.[row.k]));
            // For weight, lower is better; for others, higher is better
            const better = row.k === 'weight_kg' ? 'lower' : 'higher';
            const norm = normalize(vals, better);
            return (
              <div key={row.k} className="bg-slate-50 rounded-xl p-3">
                <div className="mb-2 text-[13px] text-slate-600 font-semibold">{row.l}</div>
                <div className="space-y-2">
                  {items.map((it, i) => (
                    <div key={it.id} className="flex items-center gap-3">
                      <div className="w-24 text-[12px] leading-tight truncate">{it.name}</div>
                      <div className="flex-1 h-2 rounded-full bg-slate-200 overflow-hidden">
                        <div className="h-full rounded-full bg-blue-500" style={{ width: `${norm[i]}%` }} />
                      </div>
                      <div className="w-12 text-right text-[12px] font-semibold">
                        {it.specs?.[row.k] ?? '—'}
                        {['ram_gb','storage_gb'].includes(row.k) ? ' GB' : row.k === 'weight_kg' ? ' kg' : row.k === 'display_size_in' ? '”' : row.k === 'display_brightness_nits' ? ' nits' : ''}
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            );
          })}
        </div>
      </div>
      <div className="sticky bottom-0 z-30 border-t bg-white/90 backdrop-blur px-3 py-2 flex items-center gap-2">
        <a href="#top" className="rounded-lg border px-3 py-2 text-sm">Sus</a>
        <a href="/compare/laptops" className="ml-auto rounded-lg bg-black px-4 py-2 text-white text-sm">Comparație avansată</a>
      </div>
    </div>
  );
}

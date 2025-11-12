import React from 'react';
import { Head, usePage } from '@inertiajs/react';
import MobileLaptopCompare from '@/Components/Compare/MobileLaptopCompare';

export default function LaptopsMobile(){
  const { props } = usePage();
  const { items = [], title = '', metrics = [
    { key: 'cpu_score', label: 'CPU', better: 'higher' },
    { key: 'gpu_score', label: 'GPU', better: 'higher' },
    { key: 'display_nits', label: 'Luminozitate', better: 'higher', unit: 'nits' },
    { key: 'battery_wh', label: 'Baterie', better: 'higher', unit: 'Wh' },
    { key: 'weight_kg', label: 'Greutate', better: 'lower', unit: 'kg' },
    { key: 'price_ron', label: 'Preț', better: 'lower', unit: 'RON' },
  ] } = props;

  return (
    <div className="container mx-auto max-w-screen-sm">
      <Head title={title} />
      <MobileLaptopCompare items={items} metrics={metrics} title={title} />
    </div>
  );
}

<x-filament-panels::page>
    <div style="margin-bottom: 4px">
        <h2 style="margin: 0; font: 800 22px 'DM Sans', sans-serif; color: #0F1827">Gestión de propuestas</h2>
        <p style="margin: 6px 0 0; color: #64748B; font-size: 14px; max-width: 860px; line-height: 1.5">
            Configura las opciones que usan los docentes al preparar sus propuestas.
        </p>
    </div>

    <section style="padding: 18px 20px; border: 1px solid #DDE3EE; border-radius: 16px; background: #fff">
        <h3 style="margin: 0; font: 800 16px 'DM Sans', sans-serif; color: #0F1827">Categorías de gasto</h3>
        <p style="margin: 4px 0 14px; color: #64748B; font-size: 13px; line-height: 1.5">
            Las que el docente elige al agregar un concepto al presupuesto. Arrastra para cambiar el orden. Una categoría que ya usa algún concepto no se elimina: deja de ofrecerse y los conceptos la conservan.
        </p>
        {{ $this->table }}
    </section>
</x-filament-panels::page>

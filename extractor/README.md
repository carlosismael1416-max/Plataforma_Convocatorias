# Motor de búsqueda y extracción — ITSVA

Parte en Python de la Plataforma de Convocatorias ITSVA: busca convocatorias,
las extrae de las fuentes oficiales, las clasifica, valida y publica en
PostgreSQL (la misma base de la web Laravel).

## Puesta en marcha

```bash
python3 -m venv .venv
./.venv/bin/pip install -r requirements.txt
cp .env.example .env        # datos de conexión a PostgreSQL
```

- Ciclo completo (buscar → extraer → validar → publicar):
  `./.venv/bin/python -m app.ejecutar_ciclo_automatico`
- API para la web (solo en 127.0.0.1):
  `./.venv/bin/uvicorn app.main:app --host 127.0.0.1 --port 8001`
- Servicios de systemd (ciclo cada 6 h, API y respaldo): carpeta `systemd/`.

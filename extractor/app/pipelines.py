
import logging

from app.guardar_resultado_scrapy import (
    guardar_resultado_scrapy,
)


class GuardarConvocatoriasPipeline:
    """
    Guarda en PostgreSQL las convocatorias
    extraídas por Scrapy.
    """

    def __init__(self, fuente_id):
        self.fuente_id = int(fuente_id)
        self.logger = logging.getLogger(__name__)

    @classmethod
    def from_crawler(cls, crawler):
        fuente_id = crawler.settings.getint(
            "FUENTE_ID",
            0,
        )

        if fuente_id <= 0:
            raise ValueError(
                "Debes configurar FUENTE_ID "
                "para activar el guardado."
            )

        return cls(fuente_id=fuente_id)

    def process_item(self, item):
        resultado_db = guardar_resultado_scrapy(
            resultado=dict(item),
            fuente_id=self.fuente_id,
        )

        item["guardado_db"] = resultado_db

        self.logger.info(
            "Guardado de %s: %s",
            item.get("titulo"),
            resultado_db["estado"],
        )

        return item

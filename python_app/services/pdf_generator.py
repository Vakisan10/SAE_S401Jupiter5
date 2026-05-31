"""
Service de génération de PDF pour les devis.
Nécessite: pip install reportlab pillow
"""
import io
import os
from datetime import datetime

try:
    from reportlab.lib.pagesizes import A4
    from reportlab.lib.units import mm
    from reportlab.lib.colors import HexColor, black, white
    from reportlab.pdfgen import canvas
    from reportlab.lib.styles import getSampleStyleSheet
    REPORTLAB_AVAILABLE = True
except ImportError:
    REPORTLAB_AVAILABLE = False


BLUE = HexColor('#1a3a6e') if REPORTLAB_AVAILABLE else None
LIGHT_GRAY = HexColor('#f5f5f5') if REPORTLAB_AVAILABLE else None
DARK_GRAY = HexColor('#333333') if REPORTLAB_AVAILABLE else None
GREEN = HexColor('#27ae60') if REPORTLAB_AVAILABLE else None
ORANGE = HexColor('#e67e22') if REPORTLAB_AVAILABLE else None
RED = HexColor('#e74c3c') if REPORTLAB_AVAILABLE else None


class PdfGenerator:
    """Génère des PDFs pour les devis."""

    def generer_devis(self, devis: dict) -> bytes:
        """Génère le PDF d'un devis et retourne les bytes."""
        if not REPORTLAB_AVAILABLE:
            raise ImportError("reportlab n'est pas installé. Exécutez: pip install reportlab")

        buffer = io.BytesIO()
        c = canvas.Canvas(buffer, pagesize=A4)
        width, height = A4

        self._ajouter_entete(c, devis, width, height)
        self._ajouter_demandeur(c, devis, width, height)
        self._ajouter_fournisseur(c, devis, width, height)
        self._ajouter_details(c, devis, width, height)
        self._ajouter_statut(c, devis, width, height)
        self._ajouter_budget(c, devis, width, height)
        self._ajouter_pied_de_page(c, devis, width, height)

        c.save()
        buffer.seek(0)
        return buffer.read()

    def _ajouter_entete(self, c, devis, width, height):
        # Logo
        logo_path = os.path.join(os.path.dirname(__file__), '..', 'static', 'img', 'logo-iutv.jpg')
        if os.path.exists(logo_path):
            c.drawImage(logo_path, 20 * mm, height - 45 * mm, width=35 * mm, preserveAspectRatio=True)

        # Titre
        c.setFillColor(DARK_GRAY)
        c.setFont('Helvetica-Bold', 20)
        c.drawString(65 * mm, height - 30 * mm, 'DEMANDE DE DEVIS')

        c.setFont('Helvetica', 11)
        c.drawString(65 * mm, height - 40 * mm, f"IUT de Villetaneuse – Sorbonne Université")

        # Numéro et date
        c.setFillColor(BLUE)
        c.setFont('Helvetica-Bold', 14)
        c.drawRightString(width - 20 * mm, height - 30 * mm,
                          f"Devis #{str(devis.get('id_devis', 0)).zfill(4)}")
        c.setFont('Helvetica', 10)
        c.setFillColor(DARK_GRAY)
        date_str = ''
        if devis.get('date_creation'):
            try:
                d = devis['date_creation']
                if hasattr(d, 'strftime'):
                    date_str = d.strftime('%d/%m/%Y')
                else:
                    date_str = str(d)[:10]
            except Exception:
                date_str = str(devis.get('date_creation', ''))
        c.drawRightString(width - 20 * mm, height - 40 * mm, f"Date : {date_str}")

        # Ligne de séparation
        c.setStrokeColor(BLUE)
        c.setLineWidth(2)
        c.line(20 * mm, height - 50 * mm, width - 20 * mm, height - 50 * mm)

    def _ajouter_demandeur(self, c, devis, width, height):
        y = height - 65 * mm
        c.setFont('Helvetica-Bold', 12)
        c.setFillColor(BLUE)
        c.drawString(20 * mm, y, 'DEMANDEUR')
        y -= 8 * mm
        c.setFont('Helvetica', 10)
        c.setFillColor(DARK_GRAY)
        c.drawString(20 * mm, y, f"Département : {devis.get('departement_nom', 'N/A')}")
        y -= 6 * mm
        c.drawString(20 * mm, y, f"Demandeur   : {devis.get('demandeur_nom', 'N/A')}")

    def _ajouter_fournisseur(self, c, devis, width, height):
        y = height - 65 * mm
        c.setFont('Helvetica-Bold', 12)
        c.setFillColor(BLUE)
        c.drawString(110 * mm, y, 'FOURNISSEUR')
        y -= 8 * mm
        c.setFont('Helvetica', 10)
        c.setFillColor(DARK_GRAY)
        c.drawString(110 * mm, y, f"Nom     : {devis.get('fournisseur_nom', 'N/A')}")
        y -= 6 * mm
        c.drawString(110 * mm, y, f"Contact : {devis.get('fournisseur_contact', 'N/A')}")
        y -= 6 * mm
        c.drawString(110 * mm, y, f"Email   : {devis.get('fournisseur_email', 'N/A')}")

    def _ajouter_details(self, c, devis, width, height):
        y = height - 100 * mm
        c.setFont('Helvetica-Bold', 12)
        c.setFillColor(BLUE)
        c.drawString(20 * mm, y, 'DÉTAILS DE LA DEMANDE')
        y -= 8 * mm
        c.setFont('Helvetica', 10)
        c.setFillColor(DARK_GRAY)
        description = devis.get('description', 'Aucune description')
        # Découpe la description en lignes de ~80 chars
        words = str(description).split()
        line = ''
        for word in words:
            if len(line) + len(word) + 1 > 80:
                c.drawString(20 * mm, y, line)
                y -= 5 * mm
                line = word
            else:
                line = (line + ' ' + word).strip()
        if line:
            c.drawString(20 * mm, y, line)
            y -= 5 * mm
        y -= 3 * mm
        c.drawString(20 * mm, y, f"Quantité : {devis.get('quantite', 1)}")
        y -= 6 * mm
        prix = devis.get('prix_unitaire', 0)
        try:
            prix_fmt = f"{float(prix):,.2f} €".replace(',', ' ')
        except Exception:
            prix_fmt = f"{prix} €"
        c.drawString(20 * mm, y, f"Prix unitaire : {prix_fmt}")

    def _ajouter_statut(self, c, devis, width, height):
        y = height - 155 * mm
        statut = devis.get('statut', 'en_attente')
        couleurs = {
            'valide': GREEN,
            'signe': GREEN,
            'refuse': RED,
            'en_attente': ORANGE,
            'a_signer': ORANGE,
        }
        couleur = couleurs.get(statut, ORANGE)
        c.setFillColor(couleur)
        c.setFont('Helvetica-Bold', 11)
        c.drawString(20 * mm, y, f"Statut : {statut.replace('_', ' ').capitalize()}")

    def _ajouter_budget(self, c, devis, width, height):
        y = height - 170 * mm
        c.setFont('Helvetica-Bold', 12)
        c.setFillColor(BLUE)
        c.drawString(20 * mm, y, 'BUDGET')
        y -= 8 * mm
        c.setFont('Helvetica', 10)
        c.setFillColor(DARK_GRAY)
        try:
            montant = float(devis.get('prix_unitaire', 0)) * int(devis.get('quantite', 1))
            montant_fmt = f"{montant:,.2f} €".replace(',', ' ')
        except Exception:
            montant_fmt = "N/A"
        c.drawString(20 * mm, y, f"Montant total estimé : {montant_fmt}")

    def _ajouter_pied_de_page(self, c, devis, width, height):
        c.setStrokeColor(BLUE)
        c.setLineWidth(1)
        c.line(20 * mm, 20 * mm, width - 20 * mm, 20 * mm)
        c.setFont('Helvetica', 8)
        c.setFillColor(DARK_GRAY)
        c.drawString(20 * mm, 14 * mm, 'SAE Suivi Colis – IUT de Villetaneuse – Sorbonne Université')
        c.drawRightString(width - 20 * mm, 14 * mm,
                          f"Généré le {datetime.now().strftime('%d/%m/%Y à %H:%M')}")

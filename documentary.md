# Dokumentacja: customflags.php

## Opis

Plik główny modułu, odpowiedzialny za:

- Rejestrację hooków.
- Obsługę konfiguracji modułu.
- Implementację logiki dodawania, edycji oraz usuwania flag.
- Zarządzanie globalnymi flagami i przypisywaniem ich do produktów lub kategorii.

# Wszystkie zarejestrowane hooki

Moduł rejestruje następujące hooki w systemie PrestaShop:

1. **`actionProductFormBuilderModifier`**

   - Dodaje możliwość wyboru flag w formularzu edycji produktów. Forularz stworzony dzięki `FormBuilder` (wcześniej próbowałem napisania własnego formularza, ale nie działało to poprawnie więc po researchu zdecydowałem się na użycie wbudowanego).

2. **`actionCategoryFormBuilderModifier`**

   - Umożliwia przypisywanie flag w formularzu edycji kategorii. Analogicznie do `actionProductFormBuilderModifier`. Obsługuje także zapisanie przypisanych flag do kategorii przy edycji kategorii.

3. **`actionProductFlagsModifier`**

   - Hook odpowiedzialny za wyświetlanie dla każdego produktu przypisanych flag.

4. **`actionAfterUpdateProductFormHandler`**

   - Obsługuje zapisanie przypisanych flag po aktualizacji produktu funkcją `processFlags`.

5. **`actionAfterCreateProductFormHandler`**

   - Obsługuje zapisanie przypisanych flag podczas tworzenia nowego produktu funkcją `processFlags`.

6. **`actionProductDelete`**

   - Usuwa wszystkie flagi przypisane do produktu po jego usunięciu.

7. **`actionCategoryAdd`**

   - Obsługuje zapisanie przypisanych flag podczas dodawania nowej kategorii.

8. **`actionCategoryDelete`**

   - Usuwa wszystkie flagi przypisane do kategorii po jej usunięciu.

9. **`actionProductFlagsModifier`**

   - Hook odpowiedzialny za wyświetlanie flag przy produkcie. Zastosowałem tutaj referencje do `&$params['flags']` który przechowuje flagi jakie ma produkt, aby bezpośrednio modyfikować tablicę z flagami jaka znajduje przypisana do produktu. Dzięki czemu styl flag jest taki sam jak jest ustawiony w stylu sklepu z domyślnymi flagami jak np. "Nowość".

## Główne funkcje

1. **getContent()**  
   Generuje widok konfiguracji modułu w panelu administracyjnym. Obsługuje:

   - Dodawanie nowych flag.
   - Edycję istniejących flag.
   - Usuwanie flag.
   - Ustawianie flag jako globalne.

2. **Hooki**
   - `hookActionProductFormBuilderModifier`: Dodaje pole wyboru flag w formularzu produktu.
   - `hookActionAfterUpdateProductFormHandler`: Zapisuje przypisane flagi po aktualizacji produktu.
   - `hookActionAfterCreateProductFormHandler`: Zapisuje przypisane flagi podczas tworzenia nowego produktu.
   - `hookActionProductFlagsModifier`: Zarządza wyświetlaniem flag na froncie sklepu.
   - `hookActionCategoryAdd`: Zapisuje przypisane flagi podczas dodawania nowej kategorii.
   - `hookActionCategoryDelete`: Usuwa flagi przypisane do kategorii po jej usunięciu.

## Integracja z bazą danych

Tworzy tabele:

- `custom_flags`: Przechowuje definicje flag.
- `custom_flag_product`: Mapuje flagi na produkty.
- `custom_flag_category`: Mapuje flagi na kategorie.

---

# Dokumentacja: classes/CustomFlag.php

## Główne metody

1. **getFlags()**  
   Pobiera listę wszystkich dostępnych flag.

2. **getFlag($id_flag)**  
   Pobiera szczegóły flagi o podanym ID.

3. **removeFlag($id_flag)**  
   Usuwa flagę o podanym ID.

4. **assignFlagToProduct($id_flag, $id_product)**  
   Przypisuje flagę do konkretnego produktu.

5. **assignFlagToCategory($id_flag, $id_category)**  
   Przypisuje flagę do konkretnej kategorii.

6. **checkIfCondition($condition, $countOfProducts)**  
   Sprawdza, czy produkt spełnia warunek przypisany do flagi.

## Struktura bazy danych

## Struktura bazy danych

Moduł tworzy trzy tabele w bazie danych:

### **Tabela `custom_flags`**

Przechowuje definicje flag.

- **`id_flag`** Unikalny identyfikator flagi.
- **`name`** Nazwa flagi, widoczna w interfejsie użytkownika.
- **`condition`** Opcjonalne warunki wyświetlania flagi (np. stan magazynowy).
- **`is_global`** Określa, czy flaga jest przypisana globalnie (do wszystkich produktów).
- **`date_add`** Data utworzenia wpisu.
- **`date_upd`** Data ostatniej modyfikacji wpisu.

---

### **Tabela `custom_flag_product`**

Mapuje flagi na produkty.

- **`id_flag_product`** Unikalny identyfikator przypisania flagi do produktu.
- **`id_flag`** Identyfikator flagi z tabeli `custom_flags`.
- **`id_product`** Identyfikator produktu, do którego przypisano flagę.
- **`date_add`** Data przypisania flagi do produktu.

---

### **Tabela `custom_flag_category`**

Mapuje flagi na kategorie.

- **`id_flag_category`** Unikalny identyfikator przypisania flagi do kategorii.
- **`id_flag`** Identyfikator flagi z tabeli `custom_flags`.
- **`id_category`** Identyfikator kategorii, do której przypisano flagę.
- **`date_add`** Data przypisania flagi do kategorii.

---

# Dokumentacja: views/templates/admin/configure.tpl

## Opis

Plik szablonu odpowiedzialny za generowanie widoku konfiguracji modułu w panelu administracyjnym.

## Elementy widoku

1. **Formularz dodawania flag**

   - Pole nazwy flagi.
   - Opcjonalne ustawienia warunku (np. stan magazynowy).

2. **Lista istniejących flag**

   - Przycisk edycji.
   - Przycisk usuwania.
   - Przycisk ustawiania flagi jako globalnej.

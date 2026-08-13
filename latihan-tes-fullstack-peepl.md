# 📘 Paket Latihan Tes Rekrutmen — Fullstack Developer (Project)

> Persiapan tes kemampuan teknis + psikotes.
> Kerjakan dulu tanpa melihat kunci jawaban, lalu cocokkan dan baca pembahasannya.
> Kunci jawaban ada di bagian paling bawah.

***

# BAGIAN A — TES KEMAMPUAN TEKNIS (Fullstack Developer)

A1. JavaScript Fundamental

**Soal 1.** Apa output dari kode berikut?

```javascript
console.log(typeof null);
console.log(typeof undefined);
```

A. `"null"` dan `"undefined"`
B. `"object"` dan `"undefined"`
C. `"object"` dan `"null"`
D. `"null"` dan `"object"`

**Soal 2.** Apa output dari kode berikut?

```javascript
console.log(a);
var a = 5;

console.log(b);
let b = 10;
```

A. `5` dan `10`
B. `undefined` dan `10`
C. `undefined` dan ReferenceError
D. ReferenceError dan ReferenceError

**Soal 3.** Apa output dari kode berikut?

```javascript
function counter() {
  let count = 0;
  return function () {
    count++;
    return count;
  };
}
const c = counter();
c();
c();
console.log(c());
```

A. 1
B. 2
C. 3
D. undefined

**Soal 4.** Apa urutan output dari kode berikut?

```javascript
console.log("A");
setTimeout(() => console.log("B"), 0);
Promise.resolve().then(() => console.log("C"));
console.log("D");
```

A. A, B, C, D
B. A, D, B, C
C. A, D, C, B
D. A, C, D, B

# **Soal 5.** Manakah perbandingan yang menghasilkan `true`

![1.00]()

?
A. `null === undefined`
B. `"5" === 5`
C. `"5" == 5`
D. `NaN == NaN`
===============

**Soal 6.** Apa output dari kode berikut?

```javascript
const arr = [1, 2, 3, 4, 5];
const result = arr.filter(x => x % 2 === 0).map(x => x * 10);
console.log(result);
```

A. `[2, 4]`
B. `[20, 40]`
C. `[10, 20, 30, 40, 50]`
D. `[2, 4, 20, 40]`

**Soal 7.** Jelaskan singkat: apa itu **event loop** di JavaScript dan mengapa JavaScript disebut *single-threaded* tapi bisa menangani operasi asynchronous?

**Soal 8.** Apa output dari kode berikut?

```javascript
const obj1 = { a: 1, b: 2 };
const obj2 = { ...obj1, b: 5, c: 3 };
console.log(obj2);
```

A. `{ a: 1, b: 2 }`
B. `{ a: 1, b: 5, c: 3 }`
C. `{ a: 1, b: 2, b: 5, c: 3 }`
D. Error

***

## A2. HTML & CSS

**Soal 9.** Tag HTML manakah yang paling **semantik** untuk membungkus konten utama sebuah halaman?
A. `<div id="main">`
B. `<section>`
C. `<main>`
D. `<body>`

**Soal 10.** Selector CSS mana yang memiliki **specificity tertinggi**?
A. `.card .title`
B. `#header .title`
C. `div.title`
D. `.title`

**Soal 11.** Tuliskan CSS untuk membuat sebuah `div` berada **tepat di tengah** (horizontal & vertikal) parent-nya menggunakan Flexbox.

**Soal 12.** Apa perbedaan `position: absolute` dan `position: fixed`?

***

## A3. Backend & REST API

**Soal 13.** Method HTTP manakah yang bersifat **idempotent** (dipanggil berkali-kali hasilnya sama)?
A. POST saja
B. GET, PUT, DELETE
C. POST dan PUT
D. Hanya GET

**Soal 14.** Cocokkan status code dengan artinya:

* `201`, `400`, `401`, `403`, `404`, `500`

**Soal 15.** Anda membuat API untuk resource `users`. Tuliskan endpoint RESTful untuk:

1. Mengambil semua user
2. Mengambil user dengan id 5
3. Membuat user baru
4. Mengupdate sebagian data user id 5
5. Menghapus user id 5

**Soal 16.** Apa perbedaan **authentication** dan **authorization**? Berikan contoh implementasi masing-masing (misal: JWT, role-based access).

**Soal 17.** Dalam Express.js, apa fungsi **middleware**? Tuliskan contoh middleware sederhana untuk logging request.

***

## A4. Database

**Soal 18.** Diberikan tabel:

* `users(id, name, email)`

* `orders(id, user_id, total, created_at)`

Tuliskan query SQL untuk menampilkan **nama user beserta total belanja keseluruhannya**, hanya untuk user yang total belanjanya di atas 1.000.000, diurutkan dari yang terbesar.

**Soal 19.** Apa perbedaan `WHERE` dan `HAVING` di SQL?

**Soal 20.** Apa itu masalah **N+1 query** dan bagaimana cara menghindarinya (misal di ORM)?

**Soal 21.** Kapan Anda memilih **NoSQL (misal MongoDB)** dibanding **SQL (misal PostgreSQL)**? Berikan 2 contoh kasus masing-masing.

***

## A5. Git & Tools

**Soal 22.** Apa perbedaan `git merge` dan `git rebase`? Kapan sebaiknya rebase **tidak** digunakan?

**Soal 23.** Anda sudah terlanjur `git commit` (belum push) dan ingin membatalkan commit terakhir **tanpa menghilangkan perubahan filenya**. Perintah apa yang digunakan?

***

## A6. Coding Challenge (kerjakan dengan bahasa apa pun, JavaScript disarankan)

**Soal 24.** Buat fungsi `isPalindrome(str)` yang mengembalikan `true` jika string adalah palindrome (abaikan spasi dan huruf besar/kecil).

```
isPalindrome("Kasur rusak") → true
isPalindrome("Halo") → false
```

**Soal 25.** Buat fungsi `findDuplicate(arr)` yang mengembalikan array berisi angka yang muncul lebih dari sekali.

```
findDuplicate([1, 2, 3, 2, 4, 4, 5]) → [2, 4]
```

**Soal 26.** FizzBuzz: cetak angka 1–100. Kelipatan 3 cetak "Fizz", kelipatan 5 cetak "Buzz", kelipatan keduanya cetak "FizzBuzz".

**Soal 27.** Buat fungsi async `getUserNames(url)` yang melakukan `fetch` ke API, mengambil array user, dan mengembalikan array berisi nama-namanya saja, dengan error handling.

***

***

# BAGIAN B — PSIKOTES

## B1. Tes Numerik — Deret Angka

**Soal 28.** 2, 4, 8, 16, 32, ... = ?
A. 48  B. 64  C. 40  D. 56

**Soal 29.** 3, 5, 9, 17, 33, ... = ?
A. 49  B. 65  C. 63  D. 55

**Soal 30.** 1, 4, 9, 16, 25, ... = ?
A. 30  B. 35  C. 36  D. 49

**Soal 31.** 7, 10, 9, 12, 11, 14, ... = ?
A. 13  B. 15  C. 12  D. 16

**Soal 32.** 100, 95, 85, 70, 50, ... = ?
A. 30  B. 25  C. 35  D. 40

## B2. Tes Numerik — Aritmetika & Soal Cerita

**Soal 33.** Sebuah laptop seharga Rp8.000.000 didiskon 20%, lalu dikenakan pajak 10% dari harga setelah diskon. Berapa harga akhirnya?
A. Rp6.400.000  B. Rp7.040.000  C. Rp7.200.000  D. Rp6.960.000

**Soal 34.** Seorang developer menyelesaikan 3 fitur dalam 6 hari. Dengan kecepatan sama, berapa hari untuk menyelesaikan 8 fitur?
A. 12  B. 14  C. 16  D. 18

**Soal 35.** Rata-rata nilai 4 tes adalah 78. Berapa nilai tes ke-5 agar rata-ratanya menjadi 80?
A. 84  B. 86  C. 88  D. 90

**Soal 36.** Jika 2x + 6 = 18, maka 4x − 3 = ?
A. 21  B. 24  C. 18  D. 27

## B3. Tes Verbal

**Soal 37.** Sinonim dari **KREDIBEL**:
A. Terpercaya  B. Terkenal  C. Cerdas  D. Kreatif

**Soal 38.** Antonim dari **FLEKSIBEL**:
A. Lentur  B. Kaku  C. Kuat  D. Rapuh

**Soal 39.** Analogi: **API : APLIKASI = ... : ...**
A. Keyboard : Mengetik
B. Jembatan : Dua kota
C. Buku : Membaca
D. Cat : Dinding

**Soal 40.** Analogi: **DATABASE : DATA = GUDANG : ...**
A. Barang  B. Bangunan  C. Pintu  D. Kunci

**Soal 41.** Sinonim dari **VALIDASI**:
A. Pengujian/Pengesahan  B. Penolakan  C. Penundaan  D. Pembatalan

## B4. Tes Logika (Silogisme & Penalaran)

**Soal 42.** Semua programmer bisa logika dasar. Sebagian programmer adalah fullstack developer. Kesimpulan yang PASTI benar:
A. Semua fullstack developer bisa logika dasar
B. Semua yang bisa logika dasar adalah programmer
C. Sebagian fullstack developer bukan programmer
D. Tidak ada kesimpulan pasti

**Soal 43.** Jika server down, maka aplikasi tidak bisa diakses. Aplikasi BISA diakses. Kesimpulan:
A. Server down
B. Server tidak down
C. Server sedang maintenance
D. Tidak dapat disimpulkan

**Soal 44.** Andi lebih tinggi dari Budi. Budi lebih tinggi dari Citra. Dodi lebih pendek dari Citra. Siapa yang paling pendek?
A. Andi  B. Budi  C. Citra  D. Dodi

**Soal 45.** Semua bug harus diperbaiki. Sebagian temuan QA adalah bug. Kesimpulan yang PASTI benar:
A. Semua temuan QA harus diperbaiki
B. Sebagian temuan QA harus diperbaiki
C. Tidak ada temuan QA yang harus diperbaiki
D. Semua bug adalah temuan QA

## B5. Tes Logika Pola (bayangkan/deskripsikan)

**Soal 46.** Pola: ○ △ □ ○ ○ △ □ ○ ○ ○ △ □ ... Gambar berikutnya setelah □ terakhir adalah?
A. ○  B. △  C. □  D. Tidak dapat ditentukan

**Soal 47.** Pola huruf: A, C, F, J, O, ... = ?
A. S  B. T  C. U  D. R

**Soal 48.** Pola: Z, X, V, T, ... = ?
A. S  B. R  C. Q  D. P

***

***

# BAGIAN C — TES KEPRIBADIAN & TIPS

## C1. Tes Kepribadian (DISC/MBTI-style)

Tidak ada jawaban benar/salah, tapi ada strategi:

1. **Konsisten** — pertanyaan yang sama sering muncul dengan redaksi berbeda. Jawaban tidak konsisten = red flag.
2. **Jawab jujur tapi profesional** — untuk posisi developer, nilai yang dicari biasanya: teliti, tahan banting, bisa kerja tim, mau belajar.
3. **Jangan semua jawaban "ekstrem"** — menjawab "sangat setuju" di semua pernyataan terlihat tidak natural.
4. **Sesuaikan dengan peran** — fullstack developer project-based butuh: problem solving, komunikasi (koordinasi dengan tim), adaptif dengan deadline.

## C2. Tips Umum Pengerjaan Tes

* **Numerik & logika**: kerjakan yang mudah dulu, jangan terpaku >2 menit di satu soal.

* **Deret angka**: cek selisih antar angka dulu (+, −, ×, pola bertingkat, ganjil-genap selang-seling).

* **Coding test**: tulis solusi yang jalan dulu (brute force), baru optimasi jika ada waktu. Sertakan edge case (input kosong, null).

* **Baca instruksi** sampai selesai sebelum mulai — banyak yang gugur karena salah format jawaban.

* Pastikan koneksi stabil, webcam berfungsi, dan ruangan tenang sebelum tes dimulai.

***

***

# 🔑 KUNCI JAWABAN & PEMBAHASAN

## Bagian A — Teknis

**1. B** — `typeof null` adalah `"object"` (bug historis JavaScript), `typeof undefined` adalah `"undefined"`.

**2. C** — `var` ter-*hoisting* (deklarasi naik, nilai belum), jadi `undefined`. `let` berada di *temporal dead zone*, jadi ReferenceError.

**3. C** — Ini adalah **closure**: `count` tetap tersimpan. Dipanggil 3 kali → 3.

**4. C** — Urutan: synchronous dulu (A, D), lalu **microtask** (Promise → C), baru **macrotask** (setTimeout → B).

**5. C** — `==` melakukan type coercion (`"5" == 5` → true). `===` strict (tipe harus sama). `NaN` tidak pernah sama dengan apa pun, termasuk dirinya.

**6. B** — filter genap: `[2, 4]` → map ×10: `[20, 40]`.

**7.** JavaScript punya satu call stack (single-threaded). Operasi async (timer, fetch, I/O) diserahkan ke Web API/Node API, lalu callback-nya masuk antrian (queue). **Event loop** terus mengecek: jika call stack kosong, ambil callback dari antrian (microtask didahulukan) dan eksekusi. Jadi async terjadi bukan karena banyak thread di JS-nya, tapi karena delegasi + antrian.

**8. B** — Spread meng-copy properti; properti yang ditulis setelahnya menimpa yang sebelumnya (`b: 5`).

**9. C** — `<main>` adalah tag semantik untuk konten utama; membantu SEO & accessibility.

**10. B** — Specificity: ID (100) > class (10) > element (1). `#header .title` = 110.

**11.**

```css
.parent {
  display: flex;
  justify-content: center; /* horizontal */
  align-items: center;     /* vertikal */
}
```

**12.** `absolute`: posisi relatif terhadap **ancestor terdekat yang positioned** (ikut scroll halaman). `fixed`: posisi relatif terhadap **viewport** (tidak ikut scroll, misal navbar melayang).

**13. B** — GET, PUT, DELETE idempotent. POST tidak (setiap call membuat resource baru).

**14.** 201 = Created (berhasil membuat resource), 400 = Bad Request, 401 = Unauthorized (belum login), 403 = Forbidden (login tapi tidak berhak), 404 = Not Found, 500 = Internal Server Error.

**15.**

```
GET    /users
GET    /users/5
POST   /users
PATCH  /users/5     (PUT untuk update penuh)
DELETE /users/5
```

**16.** **Authentication** = membuktikan SIAPA Anda (login, JWT). **Authorization** = menentukan Anda BOLEH APA (role admin vs user). Contoh: middleware verifikasi JWT (authN), lalu middleware cek `role === 'admin'` sebelum akses endpoint tertentu (authZ).

**17.** Middleware = fungsi yang berjalan di antara request masuk dan response, punya akses ke `req`, `res`, `next`.

```javascript
app.use((req, res, next) => {
  console.log(`${req.method} ${req.url}`);
  next();
});
```

**18.**

```sql
SELECT u.name, SUM(o.total) AS total_belanja
FROM users u
JOIN orders o ON o.user_id = u.id
GROUP BY u.id, u.name
HAVING SUM(o.total) > 1000000
ORDER BY total_belanja DESC;
```

**19.** `WHERE` memfilter **baris sebelum** grouping; `HAVING` memfilter **setelah** grouping (biasanya bersama agregat seperti SUM/COUNT).

**20.** N+1: query 1 untuk ambil list, lalu N query tambahan untuk relasi tiap item (misal ambil 100 user → 100 query orders). Solusi: `JOIN`, eager loading (`include` di Sequelize, `populate` di Mongoose), atau DataLoader.

**21.** SQL: data relasional & butuh integritas/transaksi (e-commerce, pembayaran, data keuangan). NoSQL: skema fleksibel, skala besar, data semi-terstruktur (log, katalog produk bervariasi, caching, real-time feed).

**22.** `merge` menggabungkan branch dengan commit merge (history bercabang). `rebase` memindahkan commit ke ujung branch lain (history linear/rapi). Rebase **jangan** dipakai di branch yang sudah di-push/dipakai orang lain (menulis ulang history).

**23.** `git reset --soft HEAD~1` (commit dibatalkan, perubahan tetap di staging).

**24.**

```javascript
function isPalindrome(str) {
  const clean = str.toLowerCase().replace(/\s+/g, "");
  return clean === clean.split("").reverse().join("");
}
```

**25.**

```javascript
function findDuplicate(arr) {
  const seen = new Set();
  const dup = new Set();
  for (const n of arr) {
    if (seen.has(n)) dup.add(n);
    seen.add(n);
  }
  return [...dup];
}
```

**26.**

```javascript
for (let i = 1; i <= 100; i++) {
  if (i % 15 === 0) console.log("FizzBuzz");
  else if (i % 3 === 0) console.log("Fizz");
  else if (i % 5 === 0) console.log("Buzz");
  else console.log(i);
}
```

**27.**

```javascript
async function getUserNames(url) {
  try {
    const res = await fetch(url);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const users = await res.json();
    return users.map(u => u.name);
  } catch (err) {
    console.error("Gagal mengambil data:", err.message);
    return [];
  }
}
```

## Bagian B — Psikotes

**28. B** — ×2 setiap langkah: 32 × 2 = **64**.

**29. B** — selisih: +2, +4, +8, +16, +32 → 33 + 32 = **65**.

**30. C** — bilangan kuadrat: 1², 2², 3², 4², 5², 6² = **36**.

**31. A** — dua pola selang-seling: (7, 9, 11, ...) naik +2 dan (10, 12, 14, ...) naik +2 → setelah 14 adalah pola ganjil: 11 + 2 = **13**.

**32. B** — selisih: −5, −10, −15, −20, −25 → 50 − 25 = **25**.

**33. B** — 8.000.000 × 0,8 = 6.400.000 → pajak 10%: 6.400.000 × 1,1 = **7.040.000**.

**34. C** — 3 fitur / 6 hari → 1 fitur = 2 hari → 8 fitur = **16 hari**.

**35. C** — total 4 tes = 4 × 78 = 312. Target total 5 tes = 5 × 80 = 400. Nilai ke-5 = 400 − 312 = **88**.

**36. A** — 2x = 12 → x = 6 → 4(6) − 3 = **21**.

**37. A** — Kredibel = dapat dipercaya.

**38. B** — Fleksibel (lentur) ↔ kaku/rigid.

**39. B** — API menghubungkan aplikasi dengan sistem lain; jembatan menghubungkan dua kota (sama-sama "penghubung").

**40. A** — Database menyimpan data; gudang menyimpan barang.

**41. A** — Validasi = pengesahan/pengecekan keabsahan.

**42. A** — Semua fullstack developer adalah programmer (himpunan bagian), dan semua programmer bisa logika dasar → pasti benar.

**43. B** — Modus tollens: P→Q, tidak Q, maka tidak P. Aplikasi bisa diakses (¬Q) → server tidak down (¬P).

**44. D** — Andi > Budi > Citra > Dodi → paling pendek **Dodi**.

**45. B** — Sebagian temuan QA adalah bug, dan semua bug harus diperbaiki → sebagian temuan QA harus diperbaiki.

**46. A** — Pola: jumlah ○ bertambah (1, 2, 3) di antara △□ → setelah "○○○ △ □" kembali ke **○** (memulai grup 4 lingkaran).

**47. C** — selisih huruf: +2, +3, +4, +5, +6 → O + 6 = **U**.

**48. B** — mundur 2 huruf: Z, X, V, T, **R**.

***

## 🎯 Skor Diri Anda

| Bagian                                      | Jumlah | Target benar                 |
| ------------------------------------------- | ------ | ---------------------------- |
| Teknis pilihan ganda (1–6, 8–10, 13, 22–23) | 13     | ≥ 10                         |
| Teknis esai/coding (7, 11–12, 14–21, 24–27) | 14     | bisa jawab ≥ 10 dengan benar |
| Numerik (28–36)                             | 9      | ≥ 7                          |
| Verbal (37–41)                              | 5      | ≥ 4                          |
| Logika (42–48)                              | 7      | ≥ 5                          |

Kalau ada bagian yang masih banyak salah, itulah prioritas belajar Anda. Ulangi latihan 2–3 hari berturut-turut sebelum tes.

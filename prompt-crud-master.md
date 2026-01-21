 Buatkan CRUD (Model, Migration, Controller, Request, Middleware (jika ada), View) untuk module Brand dengan schema di bawah ini : 



jangan lup tambahkan role menu, role permission seperti modul lainnya



untuk di form add dan edit di masing2 field tambahan html maxlength (sesuai jumlah data pada schema di masing2 field), tambahkan juga validasi di Request



tidak perlu ada pilihan status active / inactive karena sudah di kasih default



Table ms_brand {

  brand_id "bigint unsigned" [not null, pk, increment]

  brand_code varchar(50) [not null, unique]

  brand_name varchar(100) [not null]

  brand_group varchar(100) [null]

  country_origin varchar(100) [null]

  created_by "bigint unsigned" [not null]

  created_date datetime [null, default: "CURRENT_TIMESTAMP"]

  updated_by "bigint unsigned" [null]

  updated_date datetime [null]

  unique_id char(36) [not null, unique, note: "UUIDV4, di gunakan untuk Get Data dari URL"]

  is_active enum_is_active [null, default: '1']

  Indexes {

    created_by

    updated_by

    is_active

  }

}
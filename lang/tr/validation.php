<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Doğrulama Mesajları
    |--------------------------------------------------------------------------
    |
    | Aşağıdaki öğeler doğrulama sınıfı tarafından kullanılan varsayılan hata
    | mesajlarını içermektedir. `size` gibi bazı kuralların birden çok çeşidi
    | bulunmaktadır. Her biri ayrı ayrı düzenlenebilir.
    |
    */

    'accepted' => ':attribute kabul edilmelidir.',
    'accepted_if' => ':other alanı :value değerine sahipken :attribute kabul edilmelidir.',
    'after' => ':attribute değeri :date tarihinden sonra olmalıdır.',
    'after_or_equal' => ':attribute değeri :date tarihinden sonra veya eşit olmalıdır.',
    'active_url' => ':attribute geçerli bir URL olmalıdır.',
    'alpha' => ':attribute sadece harflerden oluşmalıdır.',
    'alpha_dash' => ':attribute sadece harfler, rakamlar ve tirelerden oluşmalıdır.',
    'alpha_num' => ':attribute sadece harfler ve rakamlar içermelidir.',
    'array' => ':attribute bir dizi olmalıdır.',
    'ascii' => ':attribute sadece tek bayt alfanümerik karakterler ve sembollerden oluşmalıdır.',
    'before' => ':attribute değeri :date tarihinden önce olmalıdır.',
    'before_or_equal' => ':attribute değeri :date tarihinden önce veya eşit olmalıdır.',
    'between' => [
        'array' => ':attribute :min - :max arasında nesneye sahip olmalıdır.',
        'file' => ':attribute :min - :max arasındaki kilobayt boyutunda olmalıdır.',
        'numeric' => ':attribute :min - :max arasında olmalıdır.',
        'string' => ':attribute :min - :max arasında karakterden oluşmalıdır.',
    ],
    'boolean' => ':attribute sadece doğru veya yanlış olmalıdır.',
    'can' => ':attribute değeri yetkisiz.',
    'confirmed' => ':attribute tekrarı eşleşmiyor.',
    'current_password' => 'Parola geçersiz.',
    'date' => ':attribute geçerli bir tarih olmalıdır.',
    'date_equals' => ':attribute ile :date aynı tarihler olmalıdır.',
    'date_format' => ':attribute :format biçimi ile eşleşmiyor.',
    'date_must_be_future' => 'Tarih en az {interval} gelecekte olmalıdır',
    'date_must_be_now' => 'Tarih en az {interval} olmalıdır',
    'date_must_be_past' => 'Tarih en az {interval} geçmişte olmalıdır',
    'decimal' => ':attribute :decimal basamak sayısı olmalıdır.',
    'declined' => ':attribute alanı reddedilmelidir.',
    'declined_if' => ':attribute alanı, :other alanı :value değerine sahipken reddedilmelidir.',
    'different' => ':attribute ile :other birbirinden farklı olmalıdır.',
    'digits' => ':attribute :digits haneden oluşmalıdır.',
    'digits_between' => ':attribute :min ile :max arasında haneden oluşmalıdır.',
    'dimensions' => ':attribute görsel ölçüleri geçersiz.',
    'distinct' => ':attribute alanı yinelenen bir değere sahip.',
    'doesnt_end_with' => ':attribute şunlardan biri ile bitmemelidir: :values.',
    'doesnt_start_with' => ':attribute şunlardan biri ile başlamamalıdır: :values.',
    'email' => ':attribute alanına girilen e-posta adresi geçersiz.',
    'email_contains_invalid_characters' => ':attribute alanında geçersiz karakterler içeriyor.',
    'email_username_min_length' => ':attribute alanı en az :minLength karakter olmalıdır.',
    'email_max_length' => ':attribute alanı en fazla :maxLength karakter olmalıdır.',
    'email_domain_must_be_one_of' => ':attribute alanı şunlardan biri olmalıdır: :allowedDomains.',
    'email_domain_not_allowed' => ':attribute alanına izin verilmiyor.',
    'email_typo_suggestion' => ':suggestion mi demek istediniz?',
    'ends_with' => ':attribute, şunlardan biriyle bitmelidir :values',
    'enum' => 'Seçili :attribute geçersiz.',
    'exists' => 'Seçili :attribute geçersiz.',
    'file' => ':attribute dosya olmalıdır.',
    'filled' => ':attribute alanının doldurulması zorunludur.',
    'gt' => [
        'array' => ':attribute, :value adetten fazla olmalıdır.',
        'file' => ':attribute, :value kilobayt boyutundan büyük olmalıdır.',
        'numeric' => ':attribute, :value değerinden büyük olmalıdır',
        'string' => ':attribute, :value karakterden uzun olmalıdır.',
    ],
    'gte' => [
        'array' => ':attribute, :value adet veya daha fazla olmalıdır.',
        'file' => ':attribute, :value kilobayt boyutu kadar veya daha büyük olmalıdır.',
        'numeric' => ':attribute, :value kadar veya daha fazla olmalıdır.',
        'string' => ':attribute, :value karakter kadar veya daha uzun olmalıdır.',
    ],
    'image' => ':attribute alanı resim dosyası olmalıdır.',
    'in' => ':attribute değeri geçersiz.',
    'in_array' => ':attribute alanı :other içinde mevcut değil.',
    'integer' => ':attribute tamsayı olmalıdır.',
    'ip' => ':attribute geçerli bir IP adresi olmalıdır.',
    'ipv4' => ':attribute geçerli bir IPv4 adresi olmalıdır.',
    'ipv6' => ':attribute geçerli bir IPv6 adresi olmalıdır.',
    'json' => ':attribute geçerli bir JSON değişkeni olmalıdır.',
    'lowercase' => ':attribute küçük harf olmalıdır.',
    'lt' => [
        'array' => ':attribute, :value taneden az olmalıdır.',
        'file' => ':attribute, :value kilobayt boyutundan küçük olmalıdır.',
        'numeric' => ':attribute, :value değerinden küçük olmalıdır.',
        'string' => ':attribute, :value karakterden kısa olmalıdır.',
    ],
    'lte' => [
        'array' => ':attribute, :value adet veya daha az olmalıdır.',
        'file' => ':attribute, :value kilobayt boyutu kadar veya daha küçük olmalıdır.',
        'numeric' => ':attribute, :value kadar veya daha küçük olmalıdır.',
        'string' => ':attribute, :value karakter kadar veya daha kısa olmalıdır.',
    ],
    'mac_address' => ':attribute geçerli bir MAC adresi olmalıdır.',
    'max' => [
        'array' => ':attribute değeri :max adedinden az nesneye sahip olmalıdır.',
        'file' => ':attribute değeri :max kilobayt değerinden küçük olmalıdır.',
        'numeric' => ':attribute değeri :max değerinden küçük olmalıdır.',
        'string' => ':attribute değeri :max karakterden küçük olmalıdır.',
    ],
    'max_digits' => ':attribute en fazla :max digits hane olmalıdır.',
    'mimes' => ':attribute dosya biçimi :values olmalıdır.',
    'mimetypes' => ':attribute dosya biçimi :values olmalıdır.',
    'min' => [
        'array' => ':attribute en az :min nesneye sahip olmalıdır.',
        'file' => ':attribute değeri :min kilobayt değerinden büyük olmalıdır.',
        'numeric' => ':attribute değeri :min değerinden büyük olmalıdır.',
        'string' => ':attribute değeri :min karakterden büyük olmalıdır.',
    ],
    'min_digits' => ':attribute en az :min digits hane olmalıdır.',
    'missing' => ':attribute alanı eksik olmalıdır.',
    'missing_if' => ':attribute alanı, :other alanı :value değerine sahipken eksik olmalıdır.',
    'missing_unless' => ':attribute alanı, :other alanı :value değerine sahip değilken eksik olmalıdır.',
    'missing_with' => ':attribute alanı, :values değerlerinden birinin mevcut olması durumunda eksik olmalıdır.',
    'missing_with_all' => ':attribute alanı, :values değerlerinden herhangi birinin mevcut olması durumunda eksik olmalıdır.',
    'multiple_of' => ':attribute :value değerinin katsayısı olmalıdır.',
    'not_in' => 'Seçili :attribute geçersiz.',
    'not_regex' => ':attribute biçimi geçersiz.',
    'numeric' => ':attribute sayı olmalıdır.',
    'password' => [
        'letters' => ':attribute en az bir harf içermelidir.',
        'mixed' => ':attribute en az bir büyük harf ve en az bir küçük harf içermelidir.',
        'numbers' => ':attribute en az bir rakam içermelidir.',
        'symbols' => ':attribute en az bir sembol (özel karakter) içermelidir.',
        'uncompromised' => 'Girilen :attribute bir veri sızıntısına maruz kaldı. Lütfen yeni bir :attribute seçin.',
    ],
    'present' => ':attribute alanı mevcut olmalıdır.',
    'prohibited' => ':attribute alanının doldurulması yasak.',
    'prohibited_if' => ':other alanı :value değerine sahipken :attribute alanının doldurulması yasak.',
    'prohibited_unless' => ':other alanı :values değerine sahip değilken :attribute alanının doldurulması yasak.',
    'prohibits' => ':attribute alanı, :other alanının mevcut olmasını yasaklar.',
    'regex' => ':attribute biçimi geçersiz.',
    'required' => ':attribute alanı gereklidir.',
    'required_array_keys' => ':attribute alanı şu anahtarlara sahip olmalıdır: :values.',
    'required_if' => ':attribute alanı, :other :value değerine sahip olduğunda zorunludur.',
    'required_if_accepted' => ':other alanı kabul edildiğinde :attribute alanı gereklidir.',
    'required_unless' => ':attribute alanı, :other alanı :value değerlerinden birine sahip olmadığında zorunludur.',
    'required_with' => ':attribute alanı :values varken zorunludur.',
    'required_with_all' => ':attribute alanı herhangi bir :values değeri varken zorunludur.',
    'required_without' => ':attribute alanı :values yokken zorunludur.',
    'required_without_all' => ':attribute alanı :values değerlerinden herhangi biri yokken zorunludur.',
    'same' => ':attribute ile :other eşleşmelidir.',
    'size' => [
        'array' => ':attribute :size nesneye sahip olmalıdır.',
        'file' => ':attribute :size kilobayt olmalıdır.',
        'numeric' => ':attribute :size olmalıdır.',
        'string' => ':attribute :size karakter olmalıdır.',
    ],
    'starts_with' => ':attribute şunlardan biri ile başlamalıdır: :values',
    'string' => ':attribute dizge olmalıdır.',
    'timezone' => ':attribute geçerli bir saat dilimi olmalıdır.',
    'ulid' => ':attribute geçerli bir ULID olmalıdır.',
    'unique' => ':attribute daha önceden kayıt edilmiş.',
    'uploaded' => ':attribute yüklemesi başarısız.',
    'uppercase' => ':attribute büyük harf olmalıdır.',
    'url' => ':attribute biçimi geçersiz.',
    'uuid' => ':attribute bir UUID formatına uygun olmalı.',

    /*
    |--------------------------------------------------------------------------
    | Özelleştirilmiş Doğrulama Mesajları
    |--------------------------------------------------------------------------
    |
    | Bu alanda her niteleyici (attribute) ve kural (rule) ikilisine özel hata
    | mesajları tanımlayabilirsiniz. Bu özellik, son kullanıcıya daha gerçekçi
    | metinler göstermeniz için oldukça faydalıdır.
    |
    | Örnek olarak:
    |
    | 'email.email': 'Girdiğiniz e-posta adresi geçerli değil.'
    | 'x.regex': 'x alanı için "a-b.c" formatında veri girmelisiniz.'
    |
    */

    'custom' => [
        'x' => [
            'regex' => 'x alanı için "a-b.c" formatında veri girmelisiniz.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Özelleştirilmiş Niteleyici İsimleri
    |--------------------------------------------------------------------------
    |
    | Bu alandaki bilgiler "email" gibi niteleyici isimlerini "e-posta adresi"
    | gibi daha okunabilir metinlere çevirmek için kullanılır. Bu bilgiler
    | hata mesajlarının daha temiz olmasını sağlar.
    |
    | Örnek olarak:
    |
    | 'email' => 'e-posta adresi',
    | 'password' => 'parola',
    |
    */

    'attributes' => [],

];

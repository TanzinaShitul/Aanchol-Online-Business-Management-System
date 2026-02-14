
CREATE DATABASE aanchol_db;
USE aanchol_db;

CREATE TABLE divisions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NOT NULL
);

CREATE TABLE districts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    division_id INT,
    name VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    FOREIGN KEY (division_id) REFERENCES divisions(id)
);

CREATE TABLE upazilas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    district_id INT,
    name VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    FOREIGN KEY (district_id) REFERENCES districts(id)
);


CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    division_id INT,
    district_id INT,
    upazila_id INT,
    detailed_address TEXT,
    role ENUM('admin', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (division_id) REFERENCES divisions(id),
    FOREIGN KEY (district_id) REFERENCES districts(id),
    FOREIGN KEY (upazila_id) REFERENCES upazilas(id)
);


-- divisions table
INSERT INTO divisions (name, name_en) VALUES
('ঢাকা', 'Dhaka'),
('চট্টগ্রাম', 'Chittagong'),
('খুলনা', 'Khulna'),
('রাজশাহী', 'Rajshahi'),
('বরিশাল', 'Barisal'),
('সিলেট', 'Sylhet'),
('রংপুর', 'Rangpur'),
('ময়মনসিংহ', 'Mymensingh');

-- districts table (সঠিক সিনট্যাক্স সহ)
INSERT INTO districts (division_id, name, name_en) VALUES
-- ঢাকা বিভাগ (১)
(1, 'ঢাকা', 'Dhaka'),
(1, 'গাজীপুর', 'Gazipur'),
(1, 'নারায়ণগঞ্জ', 'Narayanganj'),
(1, 'টাঙ্গাইল', 'Tangail'),
(1, 'মানিকগঞ্জ', 'Manikganj'),
(1, 'মুন্সিগঞ্জ', 'Munshiganj'),
(1, 'রাজবাড়ী', 'Rajbari'),
(1, 'শরিয়তপুর', 'Shariatpur'),
-- চট্টগ্রাম বিভাগ (২)
(2, 'চট্টগ্রাম', 'Chittagong'),
(2, 'কক্সবাজার', 'Coxs Bazar'),
(2, 'খাগড়াছড়ি', 'Khagrachhari'),
(2, 'রাঙ্গামাটি', 'Rangamati'),
(2, 'নোয়াখালী', 'Noakhali'),
(2, 'ফেনী', 'Feni'),
(2, 'লক্ষ্মীপুর', 'Lakshmipur'),
(2, 'কুমিল্লা', 'Cumilla'),
-- খুলনা বিভাগ (৩)
(3, 'খুলনা', 'Khulna'),
(3, 'বাগেরহাট', 'Bagerhat'),
(3, 'যশোর', 'Jessore'),
(3, 'সাতক্ষীরা', 'Satkhira'),
-- রাজশাহী বিভাগ (৪)
(4, 'রাজশাহী', 'Rajshahi'),
(4, 'নাটোর', 'Natore'),
(4, 'নওগাঁ', 'Naogaon'),
(4, 'পাবনা', 'Pabna'),
(4, 'বগুড়া', 'Bogra'),
(4, 'সিরাজগঞ্জ', 'Sirajganj'),
-- বরিশাল বিভাগ (৫)
(5, 'বরিশাল', 'Barisal'),
(5, 'পটুয়াখালী', 'Patuakhali'),
(5, 'ভোলা', 'Bhola'),
(5, 'ঝালকাটি', 'Jhalokati'),
(5, 'পিরোজপুর', 'Pirojpur'),
-- সিলেট বিভাগ (৬)
(6, 'সিলেট', 'Sylhet'),
(6, 'সুনামগঞ্জ', 'Sunamganj'),
(6, 'মৌলভীবাজার', 'Moulvibazar'),
(6, 'হবিগঞ্জ', 'Habiganj'),
-- রংপুর বিভাগ (৭)
(7, 'রংপুর', 'Rangpur'),
(7, 'দিনাজপুর', 'Dinajpur'),
(7, 'ঠাকুরগাঁও', 'Thakurgaon'),
(7, 'পঞ্চগড়', 'Panchagarh'),
(7, 'কুড়িগ্রাম', 'Kurigram'),
(7, 'গাইবান্ধা', 'Gaibandha'),
(7, 'লালমনিরহাট', 'Lalmonirhat'),
-- ময়মনসিংহ বিভাগ (৮)
(8, 'ময়মনসিংহ', 'Mymensingh'),
(8, 'জামালপুর', 'Jamalpur'),
(8, 'শেরপুর', 'Sherpur'),
(8, 'নেত্রকোণা', 'Netrokona');

-- upazilas table (সঠিক সিনট্যাক্স সহ)
INSERT INTO upazilas (district_id, name, name_en) VALUES
-- ঢাকা (district_id 1)
(1, 'ঢাকা সদর', 'Dhaka Sadar'),
(1, 'মিরপুর', 'Mirpur'),
(1, 'উত্তরা', 'Uttara'),
(1, 'ধানমন্ডি', 'Dhanmondi'),
(1, 'মোহাম্মদপুর', 'Mohammadpur'),
(1, 'আদাবর', 'Adabor'),
(1, 'বনশ্রী', 'Banasree'),
(1, 'গুলশান', 'Gulshan'),
(1, 'বাংলামোটর', 'Banglmotor'),
(1, 'কেরানীগঞ্জ', 'Keraniganj'),
(1, 'ডোহার', 'Dohar'),
-- গাজীপুর (2)
(2, 'গাজীপুর সদর', 'Gazipur Sadar'),
(2, 'কালীগঞ্জ', 'Kaliakair'),
(2, 'কাপাসিয়া', 'Kapashia'),
(2, 'শ্রীপুর', 'Sripur'),
(2, 'নারসিংদী', 'Narsingdi'),
-- নারায়ণগঞ্জ (3)
(3, 'নারায়ণগঞ্জ সদর', 'Narayanganj Sadar'),
(3, 'সোনারগাঁ', 'Sonargaon'),
(3, 'রূপগঞ্জ', 'Rupganj'),
(3, 'সিদ্ধিরগঞ্জ', 'Siddhirganj'),
-- টাঙ্গাইল (4)
(4, 'টাঙ্গাইল সদর', 'Tangail Sadar'),
(4, 'মধুপুর', 'Madhupur'),
(4, 'কালিহাতি', 'Kalihat'),
(4, 'গাজারিয়া', 'Gaziaria'),
(4, 'দেলদুয়ার', 'Delduar'),
(4, 'ঘাটাইল', 'Ghatail'),
(4, 'শাহজাদপুর', 'Shahzadpur'),
(4, 'বাসাইল', 'Basail'),
(4, 'নাগরপুর', 'Nagarpur'),
-- মানিকগঞ্জ (5)
(5, 'মানিকগঞ্জ সদর', 'Manikganj Sadar'),
(5, 'সাটিয়া', 'Satia'),
(5, 'সিঙ্গাইর', 'Singair'),
(5, 'ঘিওর', 'Ghior'),
(5, 'দৌলতপুর', 'Daulatpur'),
(5, 'শিবালয়', 'Shibalay'),
(5, 'পাংশা', 'Pangsha'),
-- মুন্সিগঞ্জ (6)
(6, 'মুন্সিগঞ্জ সদর', 'Munshiganj Sadar'),
(6, 'গজারিয়া', 'Gaziaria'),
(6, 'শ্রীনগর', 'Srinagar'),
(6, 'টিটাগড়', 'Titagarh'),
(6, 'লৌহজং', 'Lohajang'),
(6, 'রাজৈর', 'Rajair'),
-- রাজবাড়ী (7)
(7, 'রাজবাড়ী সদর', 'Rajbari Sadar'),
(7, 'কালুখালী', 'Kalukhali'),
(7, 'বালিয়াকান্দি', 'Baliakandi'),
-- শরিয়তপুর (8)
(8, 'শরিয়তপুর সদর', 'Shariatpur Sadar'),
(8, 'নরিঙ্গল', 'Nariangal'),
(8, 'জাজিরা', 'Zajira'),
(8, 'পালং', 'Paling'),
-- চট্টগ্রাম (9)
(9, 'চট্টগ্রাম সদর', 'Chittagong Sadar'),
(9, 'পটিয়া', 'Patiya'),
(9, 'রাউজান', 'Rauzan'),
(9, 'সন্দ্বীপ', 'Sandip'),
(9, 'হাটহাজারি', 'Hathazari'),
(9, 'রাঙ্গুনিয়া', 'Rangunia'),
(9, 'বোয়ালখালী', 'Boalkhali'),
(9, 'ফটিকছড়ি', 'Fatikchhari'),
(9, 'বন্দরবান', 'Bandarban'),
(9, 'কুমিল্লা', 'Cumilla'),
-- কক্সবাজার (10)
(10, 'কক্সবাজার সদর', 'Coxs Bazar Sadar'),
(10, 'টেকনাফ', 'Teknaf'),
(10, 'উখিয়া', 'Ukhia'),
(10, 'মহেশখালী', 'Maheshkhali'),
(10, 'চকরিয়া', 'Chakaria'),
(10, 'রামু', 'Ramu'),
-- খাগড়াছড়ি (11)
(11, 'খাগড়াছড়ি সদর', 'Khagrachhari Sadar'),
(11, 'দীঘিনালা', 'Dighinala'),
(11, 'মাতিরাঙ্গা', 'Matiranga'),
(11, 'রাঙ্গামাটি', 'Rangamati'),
(11, 'পানছালাইহ', 'Panchhalain'),
-- রাঙ্গামাটি (12)
(12, 'রাঙ্গামাটি সদর', 'Rangamati Sadar'),
(12, 'কাপ্তাই', 'Kaptai'),
(12, 'বাঘাইছড়ি', 'Baghaichhari'),
(12, 'বড়হাতিয়া', 'Borhatia'),
(12, 'লংগদু', 'Longdu'),
(12, 'নানিয়াছড়', 'Naniachara'),
-- নোয়াখালী (13)
(13, 'নোয়াখালী সদর', 'Noakhali Sadar'),
(13, 'কোম্পানীগঞ্জ', 'Companiganj'),
(13, 'চট্টাই', 'Chatai'),
(13, 'সেনবাগ', 'Senbag'),
(13, 'বেগমগঞ্জ', 'Begumganj'),
(13, 'সোনাইমারী', 'Sonamairy'),
-- ফেনী (14)
(14, 'ফেনী সদর', 'Feni Sadar'),
(14, 'সোনাগাজী', 'Sonagazi'),
(14, 'ফুলছেড়ি', 'Fulchhari'),
(14, 'চৌদ্দগ্রাম', 'Chhagalnaiya'),
(14, 'পরাগলছড়', 'Parshuram'),
-- লক্ষ্মীপুর (15)
(15, 'লক্ষ্মীপুর সদর', 'Lakshmipur Sadar'),
(15, 'কমলনগর', 'Kamalnagar'),
(15, 'রাইপুর', 'Raipur'),
(15, 'রামগঞ্জ', 'Ramganj'),
-- কুমিল্লা (16)
(16, 'কুমিল্লা সদর', 'Cumilla Sadar'),
(16, 'দাউদকান্দি', 'Daudkandi'),
(16, 'লাকসাম', 'Laksam'),
(16, 'চান্দিনা', 'Chandina'),
(16, 'মেঘনা', 'Meghna'),
(16, 'ব্রাহ্মণপাড়া', 'Brahmanpara'),
(16, 'বুড়িচং', 'Burichong'),
(16, 'মনোহরগঞ্জ', 'Monoharganj'),
(16, 'নিউজয়', 'Niyazganj'),
-- খুলনা (17)
(17, 'খুলনা সদর', 'Khulna Sadar'),
(17, 'খালিশপুর', 'Khalishpur'),
(17, 'কুষ্টিয়া', 'Kushtia'),
(17, 'ডুমুরিয়া', 'Dumuria'),
(17, 'ফকিরহাট', 'Fakhirhat'),
-- বাগেরহাট (18)
(18, 'বাগেরহাট সদর', 'Bagerhat Sadar'),
(18, 'চিতলমারি', 'Chitmari'),
(18, 'কালিগঞ্জ', 'Kaliganj'),
(18, 'মোরেলগঞ্জ', 'Morelganj'),
(18, 'রামপাল', 'Rampal'),
-- যশোর (19)
(19, 'যশোর সদর', 'Jessore Sadar'),
(19, 'অভয়নগর', 'Abhaynagar'),
(19, 'বাওয়ানিয়া', 'Baownia'),
(19, 'চৌগাছা', 'Chougacha'),
(19, 'মনিরামপুর', 'Manirampur'),
(19, 'শার্শা', 'Sharsha'),
-- সাতক্ষীরা (20)
(20, 'সাতক্ষীরা সদর', 'Satkhira Sadar'),
(20, 'ডেমরা', 'Debra'),
(20, 'কালারোয়া', 'Kaalaroa'),
(20, 'শ্যামনগর', 'Shyamnagar'),
(20, 'তালা', 'Tala'),
-- রাজশাহী (21)
(21, 'রাজশাহী সদর', 'Rajshahi Sadar'),
(21, 'মোহনপুর', 'Mohanpur'),
(21, 'চাঁপাইনবাবগঞ্জ', 'Chapainawabganj'),
(21, 'তানোর', 'Tanor'),
(21, 'পবা', 'Poba'),
(21, 'বাঘা', 'Bagha'),
(21, 'নাটোরবহর', 'Natore'),
-- নাটোর (22)
(22, 'নাটোর সদর', 'Natore Sadar'),
(22, 'গুরুদাসপুর', 'Gurudaspura'),
(22, 'নওগাঁ', 'Nawabganj'),
(22, 'সিংগা', 'Singa'),
-- নওগাঁ (23)
(23, 'নওগাঁ সদর', 'Nawabganj Sadar'),
(23, 'আত্রাই', 'Atrai'),
(23, 'মান্দা', 'Manda'),
(23, 'পোরাণ', 'Poran'),
(23, 'ধনবাড়ি', 'Dhanvari'),
(23, 'রানীনগর', 'Raninagar'),
-- পাবনা (24)
(24, 'পাবনা সদর', 'Pabna Sadar'),
(24, 'আটঘরিয়া', 'Atgharia'),
(24, 'ভাঙ্গুড়া', 'Bhangura'),
(24, 'চাটমোহর', 'Chatmohar'),
(24, 'ফরিদপুর', 'Faridpur'),
(24, 'সাঁথিয়া', 'Sathia'),
(24, 'সুজানগর', 'Sujanagar'),
-- বগুড়া (25)
(25, 'বগুড়া সদর', 'Bogra Sadar'),
(25, 'আদমদীঘি', 'Adamdighi'),
(25, 'ধুনট', 'Dhunot'),
(25, 'গাবতলী', 'Gabtali'),
(25, 'কাহালু', 'Kahalu'),
(25, 'নন্দীগ্রাম', 'Nandigram'),
(25, 'শাজাদপুর', 'Shajahanpur'),
(25, 'সোহাগ', 'Souhag'),
-- সিরাজগঞ্জ (26)
(26, 'সিরাজগঞ্জ সদর', 'Sirajganj Sadar'),
(26, 'উল্লাপাড়া', 'Ullapara'),
(26, 'পাঁচবিবি', 'Panchbibi'),
(26, 'ধামইরহাট', 'Dhamoirhat'),
(26, 'কাজিহা', 'Kaziha'),
(26, 'বেলকুচি', 'Belkuci'),
(26, 'রায়গঞ্জ', 'Rayiganj'),
(26, 'তাড়াশ', 'Tarash'),
-- বরিশাল (27)
(27, 'বরিশাল সদর', 'Barisal Sadar'),
(27, 'বাকেরগঞ্জ', 'Bakerganj'),
(27, 'গৌরনদী', 'Gournadi'),
(27, 'ঝালকাটি', 'Jhalokati'),
(27, 'উজিরপুর', 'Ujirpur'),
(27, 'মেহেন্দিগঞ্জ', 'Mehendiganj'),
(27, 'বটিয়াঘাটা', 'Batiagata'),
-- পটুয়াখালী (28)
(28, 'পটুয়াখালী সদর', 'Patuakhali Sadar'),
(28, 'কোলাহল', 'Kolapara'),
(28, 'মিরজুগঞ্জ', 'Mirzaganj'),
(28, 'দশমিনা', 'Dashmina'),
(28, 'গালছিরা', 'Galtira'),
-- ভোলা (29)
(29, 'ভোলা সদর', 'Bhola Sadar'),
(29, 'তজুমদ্দিন', 'Tazumuddin'),
(29, 'দক্ষিণ তালপট্টি', 'Dakhin Talpatti'),
(29, 'চরফ্যাশন', 'Charfation'),
(29, 'লালমোহন', 'Lalmohan'),
-- ঝালকাটি (30)
(30, 'ঝালকাটি সদর', 'Jhalokati Sadar'),
(30, 'কাঠালিয়া', 'Kathalia'),
(30, 'নাজিরগঞ্জ', 'Nazirgang'),
-- পিরোজপুর (31)
(31, 'পিরোজপুর সদর', 'Pirojpur Sadar'),
(31, 'নেছারাবাদ', 'Nesarabad'),
(31, 'স্বরূপকাঠি', 'Swarupkati'),
(31, 'মাতারবাড়ি', 'Matarbari'),
(31, 'ইন্দুরকানি', 'Indurkani'),
-- সিলেট (32)
(32, 'সিলেট সদর', 'Sylhet Sadar'),
(32, 'বিয়ানীবাজার', 'Beanibazar'),
(32, 'দক্ষিণ সুরমা', 'Dakhin Surma'),
(32, 'গোয়াইনঘাট', 'Gowainghat'),
(32, 'জৈন্তিয়াপুর', 'Jaintiapur'),
-- সুনামগঞ্জ (33)
(33, 'সুনামগঞ্জ সদর', 'Sunamganj Sadar'),
(33, 'দিরাই', 'Dirai'),
(33, 'তাহিরপুর', 'Tahirpur'),
(33, 'ছাতক', 'Chattak'),
-- মৌলভীবাজার (34)
(34, 'মৌলভীবাজার সদর', 'Moulvibazar Sadar'),
(34, 'কুলাউড়া', 'Kulaura'),
(34, 'বড়লেখা', 'Barlekha'),
(34, 'জুরি', 'Juri'),
(34, 'রাজনগর', 'Rajanar'),
-- হবিগঞ্জ (35)
(35, 'হবিগঞ্জ সদর', 'Habiganj Sadar'),
(35, 'বানিয়াচং', 'Baniyachang'),
(35, 'লাখাই', 'Lakhaii'),
(35, 'মাধবপুর', 'Madhobpur'),
(35, 'অজিমনগর', 'Azimganj'),
-- রংপুর (36)
(36, 'রংপুর সদর', 'Rangpur Sadar'),
(36, 'গঙ্গাচড়া', 'Gangachara'),
(36, 'মিঠাপুকুর', 'Mithapukur'),
(36, 'পীরগঞ্জ', 'Pirganj'),
(36, 'তারাগঞ্জ', 'Taraganj'),
-- দিনাজপুর (37)
(37, 'দিনাজপুর সদর', 'Dinajpur Sadar'),
(37, 'বিরল', 'Biral'),
(37, 'খানসামা', 'Khansama'),
(37, 'ফুলবাড়ি', 'Fulbari'),
(37, 'গাইবান্ধা', 'Gaibandha'),
(37, 'নবাবগঞ্জ', 'Nababganj'),
-- ঠাকুরগাঁও (38)
(38, 'ঠাকুরগাঁও সদর', 'Thakurgaon Sadar'),
(38, 'রানীসংকৈল', 'Ranisankail'),
(38, 'বালিয়াডাঙ্গি', 'Baliadangi'),
-- পঞ্চগড় (39)
(39, 'পঞ্চগড় সদর', 'Panchagarh Sadar'),
(39, 'আদিতমারী', 'Aditmarhi'),
(39, 'বোদা', 'Boda'),
(39, 'দেবীগঞ্জ', 'Debiganj'),
-- কুড়িগ্রাম (40)
(40, 'কুড়িগ্রাম সদর', 'Kurigram Sadar'),
(40, 'বাগদা', 'Bagda'),
(40, 'চিলমারী', 'Chilimaari'),
(40, 'রৌমারী', 'Rowmari'),
(40, 'নাগেশ্বরী', 'Nageshwari'),
(40, 'ভুরুঙ্গামারী', 'Bhurungamari'),
-- গাইবান্ধা (41)
(41, 'গাইবান্ধা সদর', 'Gaibandha Sadar'),
(41, 'সাদুল্লাপুর', 'Sadullapur'),
(41, 'ফুলছেড়ি', 'Fulchhari'),
(41, 'পালনপুর', 'Palnpur'),
(41, 'জঙ্গলবাড়ি', 'Jungalbari'),
-- লালমনিরহাট (42)
(42, 'লালমনিরহাট সদর', 'Lalmonirhat Sadar'),
(42, 'হাতীবান্ধা', 'Hatibandhha'),
(42, 'পাটগ্রাম', 'Patgram'),
(42, 'আদমদীঘি', 'Adamdighi'),
-- ময়মনসিংহ (43)
(43, 'ময়মনসিংহ সদর', 'Mymensingh Sadar'),
(43, 'ফুলপুর', 'Fulpur'),
(43, 'গফরগাঁও', 'Gafargaon'),
(43, 'ঈশ্বরগঞ্জ', 'Ishwarganj'),
(43, 'কিশোরগঞ্জ', 'Kishoreganj'),
(43, 'নান্দাইল', 'Nandail'),
(43, 'দেওয়ানগঞ্জ', 'Dewanganj'),
(43, 'আউশগাঁও', 'Aushgao'),
-- জামালপুর (44)
(44, 'জামালপুর সদর', 'Jamalpur Sadar'),
(44, 'মেলন্দহ', 'Melendah'),
(44, 'ইসলামপুর', 'Islampur'),
(44, 'সরিসাব', 'Sarisab'),
(44, 'বকশীগঞ্জ', 'Bakshiganj'),
-- শেরপুর (45)
(45, 'শেরপুর সদর', 'Sherpur Sadar'),
(45, 'নালিতাবাড়ি', 'Nalitabari'),
(45, 'জগদ্দল', 'Jagdal'),
(45, 'পলাশবাড়ি', 'Polashbari'),
-- নেত্রকোণা (46)
(46, 'নেত্রকোণা সদর', 'Netrokona Sadar'),
(46, 'কেন্দুয়া', 'Kendua'),
(46, 'মোহনগঞ্জ', 'Mohanganj'),
(46, 'পূর্বমেঘালয়', 'Purba Meghalai'),
(46, 'দূর্গাপুর', 'Durgapur'),
(46, 'বারহাট্টা', 'Barhatta');



INSERT INTO users (name, email, password, phone, role) VALUES
('Admin', 'admin@aanchol.com', '$2y$10$YourHashedPasswordHere', NULL, 'admin');


CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL
);


CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category_id INT,
    stock INT DEFAULT 0,
    image VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);


CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    division_id INT,
    district_id INT,
    upazila_id INT,
    detailed_address TEXT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50) DEFAULT 'Cash on Delivery',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (division_id) REFERENCES divisions(id),
    FOREIGN KEY (district_id) REFERENCES districts(id),
    FOREIGN KEY (upazila_id) REFERENCES upazilas(id)
);


CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    size VARCHAR(50),
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);


CREATE TABLE cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    product_id INT,
    quantity INT DEFAULT 1,
    size VARCHAR(50),
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);


INSERT INTO categories (name, slug) VALUES
('Bangles', 'bangles'),
('Sarees', 'sarees'),
('Panjabi', 'panjabi'),
('Dress', 'dress'),
('Bags', 'bags');
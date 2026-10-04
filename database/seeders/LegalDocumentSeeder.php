<?php

namespace Database\Seeders;

use App\Models\LegalDocument;
use Illuminate\Database\Seeder;
use App\Models\Package;

class LegalDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $englishText = <<<'TEXT'
        Privacy Policy

        The privacy policy adopted by Deema Fashion outlines how we process personal data, payment data, and other information collected from users, suppliers, third parties, or provided directly through www.deemafashion.com and the deemafashion mobile application. We understand the importance of this data and commit to protecting, respecting, and preserving your privacy. Please read the following carefully. By using our services, you agree that your data may be handled in accordance with this policy.

        Data we collect:

        - Information provided when filling out forms during account registration, social logins, subscriptions, content publication, or additional service requests.
        - Device-specific data when you install or use the deemafashion app, such as location details and your device ID.
        - Technical information about your computer or device (IP address, operating system, browser type).
        - Inputs submitted via surveys, votes, reviews, testimonials, or feedback messages.
        - Details provided when reporting issues encountered on the site or app.
        - Correspondence logs when you contact us through "Contact Us".
        - Non-personal and demographic data.
        - Transaction data for orders placed on the site/app, including fulfilment and delivery records.
        - Email and/or mobile number shared by third parties who confirmed you consented to sharing.
        - Any other data necessary to enhance your experience with our site or app.

        Why we use your data:

        - To deliver requested information, products, or services and reach out with content you consented to.
        - To offer location-based services (e.g., ads, personalized search content).
        - To fulfil obligations under any contract between you and another entity using our site, or between you and us.
        - To improve and personalize our services.
        - To present our site optimally for your device.
        - To notify you about site changes.
        - For any other valid reason that enhances your browsing experience.
        - To manage incentive programs, promotional events, competition entries, and notify winners.

        Data disclosure:

        We treat customer data with the utmost confidentiality. Sharing occurs only under the following limited circumstances and in compliance with applicable laws:

        - Third-party service and logistic partners: We cooperate with entities (e.g., logistics providers, marketing agencies) to fulfil orders. They receive only the data required to deliver the order and process cash-on-delivery collections.

        Note on payment: Deema Fashion sells physical goods delivered to homes, and payment is cash-on-delivery only. We do not collect, store, or process credit card or electronic payment details within the app.

        - Marketing and promotional initiatives: We may use your data to improve your experience and send information about relevant goods/services, service notices, new features, special offers, and events through channels such as email, web push, in-app messages, WhatsApp, phone calls, or social media.
        - Third-party advertising: We may provide data to advertisers to help them reach target audiences while honouring our commitments.
        - Contest or promotional participation may require additional data to verify eligibility. That data may be collected by us or by the sponsoring third party, with reminders to review their privacy policy.
        - Acquisition, sale, or transfer of business assets may include customer data as part of the assets transferred to new owners, following applicable law.
        - Site/app protection: We disclose data to comply with legal requirements, assist investigations, protect rights/property, and safeguard users, including sharing with companies or authorities to prevent fraud, security risks, and credit-related threats.

        Links to external sites may lead to other privacy policies for which we are not responsible; review those policies before sharing personal information.

        - Customer care and logistics partners: We may share your contact details with these partners when contracted or affiliated to provide or facilitate service, resolve issues, and ensure a smooth experience.

        Legal basis for processing:

        We process your information when a legal basis exists under the laws of the countries where we operate.

        Data processing and usage:

        - Provide the quality service you requested.
        - Track service delivery and ensure receipt.
        - Enable invoicing and payment collection.
        - Communicate with customers to collect feedback, handle complaints, or answer inquiries.
        - Inform you about updates, improvements, or new services and regional coverage.
        - Use reviews or comments in marketing (only first name and city displayed).
        - Personalize recommended products/services based on behavior and interests.
        - Fulfill legal obligations in the countries where we operate.

        Data storage:

        Data is stored when necessary under our policy and applicable laws. Our teams process it for orders, billing, support, and it may be used by affiliates or partners for logistics, technical support, or related services. Retention periods comply with local regulations and may support analysis and market studies.

        Security controls:

        We apply administrative, technical, logistical, and commercial safeguards, including encryption and access restrictions, to protect data from unauthorized access, alteration, deletion, or disclosure. You remain responsible for securing your credentials, especially on shared devices, while we implement protections on our servers.

        Access and correction:

        Customers can access and update personal details, preferences, addresses, and orders. We may keep historical copies for compliance purposes.

        Privacy policy updates:

        We may update the policy as our operations evolve. The latest version is published on the site/app and becomes effective upon publication. Continued use after updates constitutes acceptance. We may also notify you via email, WhatsApp, Telegram, or other channels.

        Account deletion:

        You may request deletion of your account and all related data by emailing support@deemafashion.com (subject: "Account Deletion Request") or contacting support via the app's listed numbers. Requests are processed within 14 business days.

        Contact us:

        Reach us through site/app contact details or email support@deemafashion.com for inquiries, complaints, feedback, or data requests.
        TEXT;

        $arabicText = <<<'TEXT'
        سياسة الخصوصية

        ان سياسة الخصوصية التي نعتمدها شركة اونلاين ديما فاشن هي القواعد التي سنقوم بموجبها بالتعامل مع أي بيانات شخصية، بيانات الدفع، وغيرها من البيانات الأخرى التي نجمعها من المستخدمين أو الموردين أو من مصادر آخرى أو تلك التي تقدمها إلينا وذلك عند استخدامك لموقع www.deemafashion.com و/او تطبيق deemafashion للجوال، ونحن ندرك أهمية هذه البيانات، ونلتزم بحماية خصوصيتك واحترامها والحفاظ عليها. يُرجى قراءة ما يلي جيداً لفهم سياستنا فيما يتعلق بالبيانات. باستخدامك لخدماتنا فإنك توافق على التعامل مع البيانات بما يتفق مع سياسة الخصوصية هذه.

        البيانات التي نقوم بجمعها:

        · البيانات التي تزودنا بها عند تعبئة النماذج والحقول في الموقع (تسجيل الحساب)، بما في ذلك البيانات التي زودتنا بها عند التسجيل لاستخدام الموقع والتسجيلات الأخرى (مثل دخول مواقع التواصل الاجتماعي المختلفة) أو الاشتراك في الخدمات التي نقدمها أو نشر المواد أو طلب خدمات أخرى.

        · في حال تحميل أو استخدام تطبيق deemafashion على هاتفك الجوال، فقد نتمكن من الوصول إلى تفاصيل تتعلق بموقعك وموقع جوالك، بما في ذلك ID الخاص بجهازك.

        · تفاصيل حول جهاز الكمبيوتر الذي تستخدمه، كعنوان بروتوكول الإنترنت IP الخاص بك ونظام التشغيل ونوع المتصفح.

        · البيانات التي تقدمها عند دخول استطلاع للرأي أو عمليات تصويت أو تقييمات أو شهادات أو ملاحظات.

        · البيانات التي تقدمها لنا، أو التي قد نجمعها منك، عندما تبلغنا عن اي مشاكل او صعوبات تواجهها عند استخدام موقعنا او تطبيقنا.

        · جمع سجل المراسلات في حال تواصلك معنا (اتصل بنا).

        · جمع بيانات عامة وديموغرافية وغير شخصية.

        · تفاصيل المعاملات التي قمت بإجرائها عبر موقعنا وتطبيقنا وتفاصيل قيامنا بتجهيز وتسليم البضائع التي طلبتها.

        · عنوان بريدك الالكتروني و/أو رقم الجوال الذي تم تزويدنا به من قبل الغير الذين اكدوا لنا استحصالهم على موافقتك على مشاركة عنوان بريدك الالكتروني.

        · أي بيانات أخرى نعتبرها ضرورية لتعزيز تجربتك في استخدام الموقع والتطبيق.

        لماذا نستخدم بياناتك:

        · لتزويدك بالمعلومات أو المنتجات أو الخدمات التي تطلبها منا أو نرى أنك قد تكون مهتماً بها، وحيث قمت بالموافقة على أن يتم الاتصال بك لمثل هذه الأغراض.

        · لتزويدك بالخدمات المعتمدة على المكان الذي توجد فيه، مثل الإعلان ونتائج البحث ومحتويات أخرى مخصصة لك.

        · لتنفيذ الالتزامات الناجمة عن أي عقود مبرمة بينك وبين أي جهة أخرى تستخدم موقعنا، أو بينك وبيننا.

        · لتحسين خدماتنا ولتقديم خدمات أفضل ومخصصة بدرجة أكبر.

        · لضمان تقديم محتوى موقعنا بأكثر الطرق فاعلية لك وللجهاز الذي تستخدمه للوصول إلى موقعنا.

        · لإخطارك بالتغييرات التي يجريها موقعنا.

        · لأي سبب آخر نراه ضرورياً لتعزيز تجربة تصفحك للموقع.

        · لإدارة البرامج التحفيزية والإعلانية وتلبية طلباتك للحصول على هذه الحوافز، و/أو للسماح لك بالمشاركة بالمسابقات وإخطارك في حالة الفوز.

        كشف ومشاركة بيانات العميل:

        تعتبر البيانات الخاصة بعملائنا ذات خصوصية عالية ومحمية وجزءاً مهماً جداً من عملنا. ولذلك فإن عملية مشاركة بياناتك ليست بالأمر السهل وغير متاحة إلا حسب ما هو مذكور أدناه فقط وبالحد الأدنى المسموح حسب القوانين والتشريعات المعمول بها حيث اننا ملتزمون بالقوانين الموضوعة حسب كل بلد نعمل فيه وحسب التعليمات الواردة في سياسة الخصوصية:

        · الطرف الثالث (الوسيط الخدمي واللوجستي): من أجل العمل على توفير خدماتنا، نتمكن من التعاون مع شركات أخرى تابعة لنا أو غير تابعين لنا (على سبيل المثال: الشركات اللوجستية لتوصيل المنتجات إليك، وكالات وشركات التسويق). يتم تزويد هؤلاء الشركاء بالبيانات الضرورية فقط لإتمام عملية التوصيل وتحصيل قيمة الطلبات نقداً عند الاستلام (Cash on Delivery).

        ملاحظة هامة حول الدفع: تطبيق Deema Fashion مخصص لبيع المنتجات المادية وتوصيلها للمنازل، وعملية الدفع تتم حصراً بشكل نقدي عند الاستلام. نحن لا نقوم بجمع، تخزين، أو معالجة أي بيانات تتعلق ببطاقات الائتمان أو وسائل الدفع الإلكتروني داخل التطبيق.

        · عمليات التسويق والعروض الترويجية: قد نعمل على استخدام البيانات لتعزيز وتطوير تجربتك في استخدام الموقع والتطبيق ولتزويدك بمعلومات عن السلع والخدمات التي قد تكون مهتماً بها، ورسائل الخدمة، والمزايا الجديدة والتحسينات والعروض الخاصة والفعاليات ذات الأهمية. ومن الممكن التواصل معك عبر وسائل مختلفة، بما في ذلك على سبيل المثال لا الحصر البريد الإلكتروني وإشعارات الويب والمنشورات والهاتف ورسائل التطبيق ورسائل الواتساب ومواقع التواصل الاجتماعي.

        · التسويق عبر طرف ثالث باستخدام البيانات: قد نقدم البيانات إلى المعلنين لمساعدتهم في الوصول إلى الجمهور المستهدف لنتمكن من تنفيذ التزامنا مع الجهات المعلنة (عبر عرض إعلاناتهم على الجمهور المستهدف).

        · المشاركة في الفعاليات والترويج والمسابقات قد تتطلب بيانات إضافية للتحقق من أهليتك، وقد يتم جمع هذه المعلومات من قبلنا أو الطرف الراعي مع تذكيرك بمراجعة سياسة الخصوصية الخاصة بالطرف الثالث لاطلاعك على استخدامها للبيانات.

        · الاستحواذ أو البيع أو التنازل: في حال تم بيع أو التنازل أو الاستحواذ على شركتنا أو جميع الأصول التابعة لنا، فستكون بيانات العملاء أحد الأصول التي يتم تحويلها للجهة أو المالك الجديد حسب القانون المتبع في الدولة التي نعمل بها.

        · حماية الموقع والتطبيق: نُفصح عن البيانات عند الامتثال للقوانين والتشريعات، وأي تحقيقات قانونية، لحماية الحقوق والممتلكات، وسلامة المستخدمين، بما في ذلك تبادل البيانات مع الشركات والهيئات الأخرى لمنع الاحتيال ومخاطر الأمن وبيانات الائتمان.

        يرجى الانتباه إلى أن بعض روابط المنتجات أو الإعلانات قد تقودك إلى مواقع أخرى تمتلك سياسات خصوصية مستقلة؛ نحن لسنا مسؤولين عن سياسات تلك المواقع، لذا تأكد من مراجعته قبل مشاركة أي بيانات.

        · خدمة العملاء وشركات الدعم اللوجستي: قد نُشارك معلومات التواصل الخاصة بك مع هذه الشركات عند التعاقد معها أو كونها تابعة لنا لتقديم الخدمة، حل المشكلات، وضمان تجربة تسوق مميزة.

        أساس قانوني لمعالجة البيانات:

        نقوم بمعالجة بياناتك طالما كان ذلك ضرورياً وفق التشريعات المعتمدة في الدول التي نعمل بها.

        معالجة البيانات واستخدامها:

        · توفير الخدمة المطلوبة بالجودة الممكنة.
        · متابعة تقديم الخدمة وضمان استلامها.
        · تمكيننا من تحصيل الفواتير وقيمها النقدية.
        · التواصل مع العملاء واستبيان آرائهم حول الخدمات والمنتجات.
        · الرد على الملاحظات أو الشكاوى أو الاستفسارات ومتابعتها.
        · إعلامك بالتحديثات أو التحسينات على الموقع والتطبيق أو تقديم خدمات جديدة أو تغطية مناطق جديدة.
        · استخدام التعليقات أو التقييمات في الحملات التسويقية مع الإشارة إلى الاسم الأول والمدينة فقط.
        · تخصيص المحتوى والعروض بناءً على سلوكك واهتماماتك.
        · الالتزام القانوني تجاه القوانين التي تلزمنا باستخدام بياناتك امتثالاً للتشريعات.

        تخزين البيانات:

        نحتفظ بالبيانات عندما يكون ذلك ضرورياً وفق السياسة والقوانين المحلية. تتم معالجتها من قبل فريقنا لتلبية الطلبات، الفواتير، الدعم، وقد يستخدمها شركاؤنا لأغراض الدعم اللوجستي أو الفني.
        نحتفظ بالبيانات لفترات تتوافق مع القوانين وقد تفيد في التحليل الإحصائي ودراسة السوق.

        معايير الحماية:

        نتخذ إجراءات إدارية وتقنية ولوجستية وتجارية لحماية البيانات من الوصول غير المصرح أو التعديل أو الحذف. نستخدم التشفير ونطبق ضوابط وصول صارمة، كما نؤكد على مسؤوليتك في حماية كلمات المرور الخاصة بحسابك، خصوصاً في الأجهزة المشتركة.

        إمكانية الوصول والتعديل:

        يمكن للعملاء الاطلاع على بيانات الحساب، التفضيلات، العناوين، وطلبات الشراء، وتحديثها عند الحاجة. قد نحتفظ بنسخ سابقة للامتثال للمتطلبات القانونية.

        تعديل سياسة الخصوصية:

        قد يحدث تغيير في سياسة الخصوصية مع تطوير خدماتنا. يتم نشر النسخة الأخيرة على الموقع والتطبيق، ويعد استمرارك في الاستخدام بعد التعديل موافقة ضمنية.
        قد نبلغ العملاء بالتعديلات عبر البريد، واتساب، تيليجرام، أو الوسائط الأخرى، ويُفضل مراجعة السياسة باستمرار.

        إمكانية حذف الحساب والبيانات:

        نحترم خصوصيتك، ويمكنك طلب حذف حسابك وجميع البيانات المرتبطة به عن طريق:

        - إرسال طلب إلى البريد الإلكتروني: support@deemafashion.com (يرجى كتابة "طلب حذف حساب" في الموضوع).
        - التواصل مع الدعم الفني عبر الأرقام الواردة في التطبيق. سنعالج الطلب خلال 14 يوم عمل على الأكثر.

        تواصل معنا:

        يمكن للعملاء التواصل معنا عبر أرقام الموقع، التطبيق، أو البريد الإلكتروني support@deemafashion.com لأي استفسارات، شكاوى، ملاحظات، أو طلبات بيانات.
        TEXT;

        $marketerTermsEn = <<<'TEXT'
        Marketer Terms & Conditions

        This document explains the rules and commitments that apply to any individual or entity that promotes products or services on behalf of Deema Fashion. By participating in our marketer programs you agree to:

        - Register with valid commercial information, including proof of authorization if you act on behalf of a company.
        - Provide accurate contact and tax details, and update them whenever they change.
        - Only promote products, campaigns, or services that Deema Fashion has explicitly approved.
        - Respect trademark, copyright, and advertising guidelines, and avoid misleading or prohibited content.
        - Follow Deema Fashion instructions about pricing, offers, messaging, and promotional mechanics.
        - Use only official channels or links we provide for campaigns and avoid redirecting users outside the platform without prior consent.
        - Protect any confidential information or marketing collateral shared with you.
        - Notify Deema Fashion immediately if you suspect any fraudulent activity or breach related to your marketing account.
        - Comply with all applicable laws regarding marketing, data privacy, electronic messaging, and contest administration.
        - Cease marketing activities if your status is suspended, your account is cancelled, or you no longer meet the eligibility requirements.

        Deema Fashion reserves the right to suspend, terminate, or update marketer eligibility, commissions, or access at any time. Compensation is subject to proper tracking and verification. Failure to comply may result in removal from the marketer program and withholding of rewards.
        TEXT;

        $marketerTermsAr = <<<'TEXT'
        شروط وأحكام المسوقين

        تشرح هذه الوثيقة القواعد والالتزامات التي تنطبق على كل فرد أو جهة تروّج لمنتجات أو خدمات ديما فاشن. من خلال مشاركتك في برامج المسوقين، فإنك توافق على ما يلي:

        · التسجيل بمعلومات تجارية صحيحة، بما في ذلك دليل التفويض في حال كنت تمثل شركة.
        · تزويدنا ببيانات اتصال وضرائب دقيقة وتحديثها عند أي تغيير.
        · الترويج فقط للمنتجات أو الحملات أو الخدمات التي وافقت عليها ديما فاشن صراحةً.
        · الالتزام بحقوق العلامات التجارية والملكية وتفادي المحتوى المضلل أو المحظور.
        · اتباع تعليمات ديما فاشن الخاصة بالتسعير، والعروض، والرسائل، وآليات الترويج.
        · استخدام القنوات الرسمية أو الروابط التي نُقدمها فقط ولا تقم بتحويل العملاء خارج المنصة بدون موافقة مسبقة.
        · حماية أي معلومات أو مواد تسويقية سرية تم مشاركتها معك.
        · إبلاغ ديما فاشن فوراً في حال لاحظت أي نشاط احتيالي أو خرق متعلق بحسابك التسويقي.
        · الالتزام بجميع القوانين المعمول بها في مجال التسويق، وخصوصية البيانات، والرسائل الإلكترونية، وإدارة المسابقات.
        · التوقف عن أي نشاط تسويقي إذا تم تعليق حسابك، أو إلغاؤه، أو إذا لم تعد تستوفي شروط الأهلية.

        ديما فاشن تحتفظ بالحق في تعليق أو إنهاء أو تعديل أهلية المسوقين أو شروط العمولات أو الوصول في أي وقت. تُصرف التعويضات بعد التحقق من الأداء الصحيح. الإخلال قد يؤدي إلى الإزالة من البرنامج وحجز المكافآت.
        TEXT;

        $termsOfUseEn = <<<'TEXT'
        Terms of Use
        Last updated: 13/06/2025

        Welcome to our website www.deemafashion.com and our mobile application deemafashion.com. We are pleased to introduce the terms of use for each. These terms include all policies, procedures, and protocols governing your use of our services via the website and mobile application.

        Your use or registration in our services constitutes your agreement to comply with the terms of use stated in this document. These terms may be amended and updated by us at any time. Your use of our website and application after publishing such amendments or updates constitutes implicit acceptance of the modified or updated terms of use.

        About the Site

        This website is an e-commerce platform that allows business institutions to display and sell products, and allows companies and individuals to purchase a variety of products.

        We reserve the right to introduce new services, update any of the services, or withdraw them at our sole discretion without any liability.

        v Registration

        1- You may register as a buyer or seller and benefit from the services if you meet the qualifying conditions, including:

        1) Buyers:

        - You must be of legal age to purchase products in your country of residence.
        - You must be able to provide a fixed and valid address in your country of residence for product delivery.

        2) Sellers:

        - You must have a registered and valid commercial registration in accordance with the laws of the country where you conduct your business.
        - Provide proof of authorization for individuals who register on the site or use it.
        - Proof of identity for the authorized person.
        - Provide supporting banking details.
        - Agree that additional requirements may apply for certain product categories.

        2- To register on the site, we will need certain information. Your registration will not be accepted if the required information is not provided. We have the right to reject any registration without stating reasons. We also have the right to conduct the necessary verification procedures to confirm your identity and registration requirements.

        3- Upon successful registration, your registration continues for an indefinite period, subject to suspension or cancellation according to the terms set out in these Terms of Use.

        v Your Obligations

        Once you access or register for the services, you agree to the following:

        1. You are responsible for maintaining the privacy and restricting access to your account and password, and you agree to be responsible for all activities that occur under your account and password.
        2. Notify us immediately of any unauthorized use of your password or account or any other breach of secure site use.
        3. Provide complete, true, accurate, and current information about yourself and your use of the services as specified by us.
        4. Do not provide any user information provided to you by us to any third party, regardless of their status (except where required or as specified by us).
        5. Agree and cooperate if information is requested to verify your eligibility or your use of the site and our services.
        6. Refrain from uploading or downloading materials and content that violate laws, regulations, legislation, ethics, values, and public morals.
        7. Refrain from uploading, downloading, or publishing prohibited and inappropriate content or materials, whether morally or religiously inappropriate or threatening to security and public safety in any form.
        8. Refrain from publishing or uploading any content related to securities (shares, bonds, checks, etc.) and content that may promote or fall under gambling.
        9. Refrain from publishing or uploading any content related to weapons, tobacco, drugs, sedatives, intoxicants, medical drugs, and chemical materials.
        10. Refrain from publishing or uploading any false, misleading, stolen, or harmful content or materials when used normally, or defaming others.
        11. Do not publish what you are not entitled to share (links, content, images, videos, etc.).
        12. Do not break or circumvent the law, infringe on the rights of others, or violate our systems, policies, and protocols related to your account.
        13. Refrain from using our services if you no longer meet the eligibility requirements or are unable to legally comply, or if your account has been suspended or canceled.
        14. Commit to delivering the products you sold unless a legal reason prevents you from doing so as stated in our policies.
        15. Commit to paying the price of the products you purchased unless a legal reason prevents you from doing so as stated in our policies.
        16. Refrain from manipulating or monopolizing product prices.
        17. Commit to not taking any action that lowers the site's rating or ranking.
        18. Refrain from using any contact information we provided to conclude a deal outside our site or through other sites to increase your sales.
        19. Do not transfer or sell your account to a third party without our written consent.
        20. Commit to not publishing unsolicited or harmful emails or messages, or viruses or technologies that harm our services and users.
        21. Finally, refrain from violating laws related to copyright, trademark, patent, ethics, advertising, database, or any intellectual property rights related to us, licensed to us, or related to others. Do not collect any private user information without their consent or circumvent any technical measures we use to provide services.

        v Intellectual Property Rights

        In addition to all rights expressly granted under these Terms of Use, intellectual property rights include the following:

        - All site contents are our property or the property of our providers, including but not limited to content, icons, texts, graphics, logos, images, audio clips, digital products, and software. We (or the providers, as applicable) retain all our rights, ownership, and interest in the site, services, and all intellectual property rights included in these Terms of Use.
        - All rights, ownership, and interests in any information, materials, or other content you provide through your use of the services, in addition to all your intellectual property rights mentioned in these Terms of Use, will become our property.
        - You may not use our trademarks without prior written consent.
        - All rights not expressly granted to you under these Terms of Use are reserved for us or our providers.

        v Warranties and Undertakings

        By using our services, you agree and undertake the following:

        1- You are the owner and authorized to grant rights and licenses to us under these Terms of Use.
        2- You have full authority to contract under these Terms of Use, and your fulfillment of your obligations does not conflict with any laws, regulations, or governmental rules to which you are subject, or any other agreements you are bound by.
        3- If you create or use an account on behalf of a company, you are authorized to act on its behalf and guarantee its compliance with these Terms of Use. We consider this account to belong to the company and under its full management.
        4- You commit to laws and regulations related to privacy and content organization, as well as all applicable laws, systems, and regulations.
        5- You will not violate the rights of others anywhere in the world (e.g., intellectual property rights whether registered or not) and will not provide content or use services that conflict with or infringe the rights of others.
        6- We provide you with services and products as-is without warranties, undertakings, or representations, and we disclaim all warranties, undertakings, and representations of all kinds, express and implied. This includes, by way of example, warranties or undertakings regarding the suitability or fitness of content for commercial purposes or for any specific or general purpose, non-infringement, or that the services are secure, error-free, uninterrupted, or provided in a timely manner. Nevertheless, we use our best efforts and accuracy in selecting products and providers to match the described quality. Therefore, we do not guarantee that product specifications or any content for any service are accurate, complete, reliable, or free of defects or errors. As a buyer, you agree that we are not responsible for inspecting or testing services provided by us or by others. As for sellers, you are responsible for reviewing the accuracy of content in your products and services and must not obstruct our efforts to provide accurate information and correct content.

        v Liability and Indemnities

        1- Nothing in these Terms of Use authorizes or permits limiting or excluding liability of any party for:

        - Fraud, including deceit committed by that party.
        - Causing death or personal injury due to that party's negligence.
        - Any other liabilities that cannot be limited or excluded under applicable law.

        2- Our company, partners, or employees, regardless of their position or nature of work, shall not be liable, whether based on a claim in contract, tort, negligence, or breach of law or these Terms of Use, for any loss of profits, loss of data or information, business interruption, financial loss, or direct, indirect, or incidental damages, even if we have been notified of the possibility of such damages.

        3- You decide that you will not hold us liable for any damage or loss arising directly or indirectly from any of the following:

        - Your use or inability to use the services.
        - Delay or interruption in providing the services.
        - Information and content we provide when you use the services.
        - Coordination, shipping, pricing, and any instructions provided by us.
        - Any error or malfunction in the services in any form.
        - Malfunction or damage to your device when using products and services sold through the site.
        - Any content or act resulting from others' use of our services.
        - Malware or viruses, if any, while attempting to access or use the services.
        - Any action or suspension we take related to your use of our services.
        - The time it takes for your listings to appear in search results or how they appear.
        - Modification of content or activity, or your loss or inability to conduct business due to any change in these Terms of Use.

        4- If any of the above cannot be applied for any reason, our total obligations, including our company, employees, partners, and suppliers, whether resulting from any claim or demand in contract, negligence, breach of legal duty, or otherwise arising from these Terms of Use, shall be limited to the minimum of:

        - The price of the product sold on the site and shipping costs.
        - The disputed fees amount, not exceeding the total fees paid to us during the 12 months preceding the action that led to liability.

        5- You must indemnify and hold us harmless, including our company, employees, partners, and suppliers, whether as a result of any claim or demand in contract, negligence, breach of legal duty, or otherwise, against any losses, expenses, or damages, including all legal fees and attorney fees, in the following cases:

        - Third-party claims and demands resulting from your use of our services.
        - Violation or breach of any of these Terms of Use in all its details.
        - Violation or breach of any applicable laws or policies, including data protection laws or anti-spam laws.
        - Infringement of third-party intellectual property rights through your content or what you publish, and the products you list on the site, or if your content includes defamation, slander, or violation of any third-party rights or privacy.

        v Suspension and Ban

        We at Deema Fashion have the right to suspend, ban, or restrict your use of the services, cancel your product orders, or delete and hide your content at our sole discretion and judgment, without prejudice to any rights or compensation and without any liability to you. Any amounts paid and received by us related to a canceled product order will be refunded.

        v Reporting Violations of Terms of Use

        If there is content that does not comply with the Terms of Use, we ask users and customers to report it, and we will verify the matter. We remain committed to ensuring that products and content on the site comply with these Terms of Use.

        v General Provisions

        1- Applicable law: These Terms of Use and any related non-contractual rights or obligations shall be governed by and interpreted under the laws applicable in the countries where we operate.

        2- Dispute resolution: If there are issues with our services, please contact us. We will resolve all problems you face as soon as possible. Note and confirm that any dispute or disagreement related to these Terms of Use will be settled through the courts of the countries in which we operate.

        3- Third-party rights: A person who is not a party to these Terms of Use has no right to enforce any of its terms.

        4- Relationship between parties: All parties to the agreement are independent parties.

        5- Assignment of rights and obligations: These Terms of Use ensure that you may not assign or transfer any of your rights or obligations under these Terms of Use, directly or indirectly, without our prior written consent as a condition precedent.

        6- Agreement: These Terms of Use and the documents referred to or incorporated in the Terms of Use represent the entire agreement between the parties regarding the subject of the agreement.

        7- Amendment of Terms of Use: No one other than us has the right to make any amendment (addition, deletion, modification) to the Terms of Use. We reserve the right to introduce any amendment to these Terms of Use at any time. We will publish the final version of the Terms of Use on the site, and it will be effective upon publication on the site or as of the effective date we determine. Your continued use of the services in the event of any changes constitutes your acceptance to comply with the amended Terms of Use.

        8- Cancellation of a term: If any term of these Terms of Use is canceled by competent courts or deemed unlawful, that term shall be canceled and the remaining terms and provisions shall remain in effect, provided it does not affect transactions conducted under them.

        9- Force majeure: No party shall be responsible for disruption, loss, damage, delay, or failure to perform due to force majeure circumstances beyond the control of any party, including but not limited to (acts of God and measures issued by legislative, judicial, or regulatory authorities of any federal or local government, or any third party supplier of goods or services to us, labor disturbances, complete power outages, or economic boycotts).

        10- Waiver of Terms of Use: Waiver of any term of these Terms of Use does not constitute waiver of other similar or dissimilar terms, and it is not continuous unless expressly stated in writing.

        11- Contact: You can contact us through "Contact Us" or via phone numbers or email.

        12- Survival of terms and conditions: All provisions stated to be effective, or that by their nature remain effective after termination, shall remain in effect after termination or suspension of your membership in the site.
        TEXT;

        $termsOfUseAr = <<<'TEXT'
        شروط الاستخدام

        التحديث الاخير 13/06/2025

        نرحب بكم في موقعنا الالكتروني www.deemafashion.com وتطبيقنا الالكتروني deemafashion.com ويسعدنا تعريفكم بشروط استخدامكم لكل منها. وتضم هذا الشروط جميع السياسات والاجراءات والبروتوكولات الخاصة باستخدامكن لخدماتنا عبر الموقع الالكتروني وتطبيق الجوال.

        ان استخدامك او تسجيلك في خدماتنا يعتبر موافقة من قبلك على الالتزام بشروط الاستخدام المذكورة في هذه الوثيقة. وهذه الشروط قابلة للتعديل والتحديث من قبلنا في أي وقت واستخدامك لموقعنا وتطبيقنا بعد نشر هذه التعديلات او التحديثات هو موافقة ضمنية من قبلك على شروط الاستخدام التي تم تعديلها او تحديثها.

        نُبذة عن الموقع

        يعد هذا الموقع منصة للتجارة الإلكترونية التي تتيح للمستخدمين من المؤسسات التجارية عرض وبيع المنتجات، كما يتيح للشركات والأفراد شراء مجموعة متنوعة من المنتجات.

        نحتفظ بحق تقديم خدمات جديدة وتحديث أي من الخدمات أو سحبها، وفقاً لتقديرنا الخاص دون اية مسؤولية.

        v      شروط التسجيل

        1-      يحق لك التسجيل كمشترٍ أو بائع والاستفادة من الخدمات إذا توفرت لديك الشروط التي تؤهلك ومنها:

        1)      المشترين:

        ·         أن تكون بالغاً السن القانونية لتتمكن من شراء المنتجات في بلد إقامتك.

        ·         أن تكون قادراً على تقديم عنوان ثابت وحقيقي في بلد اقامتك لتسليم المنتجات لك؟

        2)      البائعين:

        ·         أن يكون لديك سجل تجاري مسجل وساري المفعول وفق القوانين الخاصة بالدولة التي تمارس نشاطك التجاري فيها.

        ·         تقديم ما يثبت تفويض الأفراد الذين يقومون بالتسجيل في الموقع أو باستخدامه.

        ·         إثبات الهوية للشخص المفوض.

        ·         تقديم بيانات مصرفية داعمة.

        ·         الموافقة على أنه قد تنطبق بعض المتطلبات الإضافية لبعض الفئات من المنتجات.

        2-      للتسجيل في الموقع، سنحتاج إلى تقديم بعض المعلومات، ولن يتم قبول تسجيلك في الموقع إذا لم يتم تقديم المعلومات اللازمة لنا. لدينا الحق في رفض أي من عمليات التسجيل دون إبداء الأسباب. كما يحق لنا أيضاً القيام بعمليات التحقق اللازمة للتأكد من هويتك ومتطلبات التسجيل.

        3-      عند الانتهاء من التسجيل بنجاح، يستمر التسجيل الخاص بك لفترة غير محددة خاضعاً لاحتمال تعليقه أو إلغائه وفقاً للبنود المذكورة في شروط الاستخدام هذه .

        v      الالتزامات المتوجبة عليك:

        بمجرد وصولك للخدمات او تسجيلك فيها فانت توافق على ما يلي:

        1.       انت مسؤول عن الحفاظ على الخصوصية وتقييد الوصول إلى حسابك الخاص واستخدامه هو وكلمة المرور، والموافقة على تحمل مسؤولية جميع الأنشطة التي تتم باسم الحساب الخاص بك وكلمة المرور الخاصة بك.

        2.       ابلاغنا فورا عن أي استخدام غير مصرح به لكلمة المرور أو الحساب الخاص بك أو أي خرق آخر لمعايير الاستخدام الآمن للموقع.

        3.       تقديم المعلومات الكاملة والحقيقية والدقيقة والحالية عن نفسك وعن استخدامك للخدمات كما هو محدد من قبلنا.

        4.       عدم تقديم اي من معلومات المستخدم المقدمة لك من قبلنا للغير مهما كانت صفته ( يستثنى من ذلك ما يلزم او ما هو محدد من قبلنا ) .

        5.       الموافقة والتعاون في حال طلب معلومات للتحقق من اهليتك او استخدامك للموقع وخدماتنا.

        6.       الامتناع عن رفع او تحميل المواد والمحتوى المخالف للانظمة والقوانين والتشريعات والاخلاق والقيم والاداب العامة.

        7.       الامتناع عن رفع وتحميل ونشر المحتويات او المواد المحظورة وغير المناسبة سواء كان اخلاقيا او دينيا او تهدد الامن والسلم باي صيغة وشكل مهما كان.

        8.       الامتناع عن نشر ورفع اي محتوى يخص الاوراق المالية ( اسهم، صكوك، شيكات، وغيرها ) والمحتوى الذي قد يروج للمقامرة او يندرج تحتها.

        9.       الامتناع عن نشر ورفع اي محتوى يخص الاسلحة والتبغ والمخدرات والمنومات والمسكرات والادوية الطبية والمواد الكيميائية.

        10.   الامتناع عن نشر ورفع اي محتوى او مواد زائفة او مضللة او مسروقة او تسبب ضرر عند استخدامها بشكل طبيعي، او التشهير بالغير.

        11.   عدم نشر ما لا يحق لك مشاركته ( روابط، محتوى، صور، فيديو وغيرها )

        12.   عدم خرق القانون او التحايل عليه او الاعتداء على حقوق الغير وخرقها، وعد خرق الانظمة والسياسات والبروتوكولات الخاصة بنا والمتعلقة بحسابك الخاص.

        13.   الامتناع عن استخدام خدماتنا في حال اصبحت غير محقق لشروط اهليتك لاستخدام خدماتنا او كنت غير قادر على الالتزام قانونيا او تم ايقاف او الغاء حسابك لدينا

        14.   الالتزام بتسليم المنتجات التي تم بيعها من قبلك الا اذا وجد سبب قانوني يمنعك من ذلك وتم ذكره في سياساتنا.

        15.   الالتزام بتسديد ثمن المنتجات التي قمت بشرائها الا اذا وجد سبب قانوني يمنعك من ذلك وتم ذكره في سياساتنا.

        16.   الامتناع عن التلاعب باسعار المنتجات او احتكارها.

        17.   الالتزام بعد القيام باي اجراء او عمل يقلل من تقييم الموقع او تصنيفه.

        18.   الامتناع عن استخدام اي من معلومات الاتصال التي قمنا بتزويدك بها لعقد صفقة عبر الموقع لمحاولة زيادة مبيعاتك خارج موقعنا او عبر مواقع اخرى.

        19.   عدم نقل او بيع الحساب الخاص بك لطرف ثالث دون موافقة خطية من قبلنا.

        20.   الالتزام بعدم نشر الرسائل والمراسلات الالكترونية غير المرغوب بها او الصارة او اي فيروسات او تقنيات تضر بخدماتنا وبالمستخدمين.

        21.   الامتناع النهائي عن خرق القوانين الخاصة بحقوق النشر، العلامة التجارية، براءة الاختراع، الأخلاق، الإعلان، قاعدة البيانات، أو أي من حقوق الملكية الفكرية التي تتعلق بنا أو المرخصة لنا او التي تتعلق بالغير. وجمع اي معلومات خاصة بالمستخدمين دون موافقتهم. أو التحايل على أي من الإجراءات التقنية التي نتبعها لتقديم الخدمات.

        v      حقوق الملكية الفكرية:

        بالاضافة الى كل الحقوق التي تم منحها صراحة وفقاً لشروط الاستخدام هذه تشمل حقوق الملكية ما يلي:

        ·         ان كل محتويات الموقع تدخل ضمن ملكيتنا الخاصة أو ضمن الملكية الخاصة بمزودينا، وتشمل على سبيل المثال لا الحصر المحتوى والايقونات والنصوص والرسوم البيانية والشعارات والصور والمقاطع الصوتية والمنتجات الرقمية والبرمجيات. فنحن (أو المزودين، وفقاً لما تقتضيه الحالة) نحتفظ بجميع حقوقنا، وملكيتنا ومصلحتنا بالموقع والخدمات وبجميع حقوق الملكية الفكرية الواردة ضمن شروط الاستخدام هذه.

        ·         جميع الحقوق والملكيات والمصالح لأي من المعلومات أو المواد أو المحتويات الأخرى التي تقدمها أنت من خلال استخدامك للخدمات بالإضافة الى جميع حقوق الملكية الفكرية الخاصة بك والواردة ضمن شروط الاستخدام هذه ستصبح ملكاً لنا.

        ·         لا يحق لك استخدام العلامات التجارية الخاصة بنا دون وجود موافقة خطية مسبقة.

        ·         إن جميع الحقوق غير المخولة لك بشكل واضح في شروط الاستخدام المماثلة يتم الاحتفاظ بها لنا أو للمزودين لنا.

        v      الضمانات والتعهدات:

        بمجرد استخدامك لخدماتنا فانت توافق وتتعهد بما يلي:

        1-      انت مالك ومخول لمنح الحقوق والتراخيص لنا وفق شروط الاستخدام هذه.

        2-      لديك سلطة كاملة للتعاقد وفق شروط الاستخدام هذه وتنفيذك لالتزاماتك هذه لا يتعارض مع اي من القوانين والتشريعات واللوائح الحكومية التي تخضع لها. او مع اي اتفاقيات اخرى انت ملتزم بها.

        3-      في حال انشاء او استخدام حساب بالنيابة عن شركة فانت مخول بالتصرف نيابة عنها وتضمن التزامها بالعمل وفق شروط الاستخدام هذه ونعتبر ان هذا الحساب ملك للشركة ورهن ادارتها الكاملة.

        4-      الالتزام بالتشريعات والقوانين المتعلقة بالخصوصية وتنظيم المحتوى وكذلك الالتزام بكافة القوانين والانظمة واللوائح المطبقة.

        5-      عدم انتهاك حقوق الغير في اي مكان في العالم ( مثال حقوق الملكية الفكرية سواء كانت مسجلة ام لا ) وعدم تقديم محتوى او استخدام للخدمات يتعارض او ينتهك حقوق الغير.

        6-      نحن نقدم لك الخدمات والمنتجات على حالتها الحالية دون ضمانات او تعهدات او اقرارات ونخلي مسؤوليتنا عن جميع الضمانات والتعهدات والاقرارات بكل اشكالها الصريحة والضمنية وكمثال جميع الضمانات او التعهدات الخاصة بصلاحية او ملائمة المحتوى للاغراض التجارية او لغرض محدد او عام او عدم مخالفته لاي حقوق او كون الخدمات امنة وخالية من العيوب او تعمل بدون اعطال او سيتم تقديمها في الوقت المناسب او على العموم . ومع ذلك نحن نعمل بكل جهد ونتوخى الدقة لاختيار المنتجات ومزودي المنتجات لتطابق الوصف والجودة المرجوة. ولذلك لا نضمن ان تكون مواصفات المنتج او اي محتوى لاي خدمة دقيقاً وكاملاً او موثوقا به او خالي من العيوب والاخطاء، وعليه وبما انك كمشتري فأنت توافق على اننا غير مسؤولين عن فحص واختبار الخدمات المقدمة من قبلنا او من غيرنا. وبالنسبة للبائعين فأنتم مسؤولين عن مراجعة دقة المحتوى الوارد ضمن منتجاتكم وخدماتكم ولا تحاولون اعاقة عملنا بما يخص توخي الدقة في تقديم المعلومات والمحتوى الصحيح.

        v      المسؤولية والتعويضات

        1-      لا تتضمن سياسة الاستخدام هذه اي بند او تخويل او سماح يعفي او يحد من المسؤولية لكل الاطراف عن:

        ·         عمليات الاحتيال، بما فيها التدليس والذي يقوم به هذا الطرف.

        ·         التسبب بالوفاة أو الإصابة الشخصية نتيجة لإهمال هذا الطرف.

        ·         جميع المسؤوليات الأخرى التي لا يمكن حدها أو الإعفاء منها وفقاً للقانون المعمول به.

        2-      لا تتحمل شركتنا او شركائنا او موظفينا مهما كان مركزهم وطبيعة عملهم سواء بناءً على دعوى او مطالبة في العقد او ضرر او اهمال او خرق للقانون او شروط الاستخدام هذه المسؤولية عن اي خسارة بالارباح او فقد البيانات او المعلومات او عطل في العمل او خسارة مالية او اضرار مباشرة او غير مباشرة او عرضية  حتى وان تم اخطارنا باحتمال وجود هذه الاضرار.

        3-      انت تقرر بأنك لن تحملنا مسؤولية حدوث اي ضرر او فقدان ناتج بشكل مباشر او غير مباشر عن كل مما يلي:

        ·         استخدامك او عدم قدرتك على استخدام الخدمات.

        ·         التأخر أو الانقطاع عن تقديم الخدمات

        ·         المعلومات والمحتوى الذي نقدمه عند استخدامك للخدمات.

        ·         التنسيق والشحن والتسعير واي تعليمات مقدمة من قبلنا

        ·         وجود اي خطأ أو عطل في الخدمات باي شكل من الاشكال.

        ·         عطل او تلف الجهاز الخاص بك عند استخدام المنتجات والخدمات التي يتم بيعها عن طريق الموقع.

        ·         اي محتوى او فعل ناتج عن استخدام الغير لخدماتنا.

        ·         البرامج الضارة او الفيروسات ان وجدت اثناء محاولة الوصول او استخدام خدماتنا.

        ·         اي اجراء او تعليق نتخذه يتعلق باستخدامك للخدماتنا.

        ·         المدة الزمنية لظهور قوائمك الخاصة في نتائج البحث او شكل ظهورها.

        ·         تعديل المحتوى او النشاط او خسارتك او عدم القدرة على القيام بالاعمال التجارية نتيجة وجود اي تغيير في شروط الاستخدام هذه.

        4-      في حال عدم امكانية تطبيق اي بند مما سبق لاي سبب من الاسباب تقتصر التزاماتنا الكلية بما في ذلك شركتنا وموظفينا وشركائنا وموردينا سواء كان ذلك نتيجة لأي دعوى او مطالبة في العقد او اهمال او خرق لاي واجب قانوني او غيره ناتجة عن شروط الاستخدام هذه، لتكون بحد أقصى القيمة الدنيا لـ:

        ·         سعر المنتج المباع على الموقع وتكاليف الشحن.

        ·         مبلغ الرسوم المتنازع عليها على الا تتجاوز قيمتها اجمالي قيمة الرسوم التي تم تسديدها الينا خلال فترة 12 شهر التي تسبق الاجراء الذي ادى الى المسؤولية.

        5-      عليك الالتزام بتعويضنا وابراء ذمتنا بما في ذلك شركتنا وموظفينا وشركائنا وموردينا سواء كان ذلك نتيجة لأي دعوى او مطالبة في العقد او اهمال او خرق لاي واجب قانوني او غيره وضد اي خسائر او نفقات او اضرار بما في ذلك كافة الرسوم القانونية واتعاب المحامين وغيرها من النفقات المتعلقة بذلك، في الحالات التالية:

        ·         ادعاءات ومطالبات الغير نتيجة استخدامك لخدماتنا.

        ·         مخالفة وخرق اي من شروط واحكام شروط الاستخدام هذه بكافة تفاصيلها.

        ·         خرق وانتهاك اي من القوانين والسياسات المعمول بها بما في ذلك قوانين حماية البيانات أو قوانين مكافحة البريد الإلكتروني العشوائي.

        ·         انتهاك حقوق الملكية الفكرية للغير من خلال محتواك او ما تقوم بنشره والمنتجات التي تقوم بادراجها في الموقع او اذا كان المحتوى الخاص بك فيه تعدي او قذف او افتراء او انتهاك لاي من حقوق الغير او خصوصيتهم.

        v      التوقيف والحظر:

        يحق لنا في تيك موول توقيف او حظر او الحد استخدامك للخدمات او الغاء طلباتك للمنتجات او حذف واخفاء المحتوى الخاص بك وفقا لتقديرنا ورؤيتنا الخاصة. مع عدم المساس بأيٍّ من الحقوق أو التعويضات أو دون أي مسؤولية تجاهك، وسيتم رد أي مبالغ تم دفعها واستلامها من قبلنا فيما يتعلق بطلب منتجات تم إلغائه.

        v      الإبلاغ عن انتهاك شروط الاستخدام:

        في حال وجود محتوى لا يتناسب مع شروط الاستخدام نرجو المستخدمين والعملاء لتبليغنا بذلك وسنقوم بالتحقق من الأمر. مع التزامنا بامتثال المنتجات والمحتوى الوارد في الموقع لشروط الاستخدام هذه.

        v      أحكام عامة:

        1-      القانون المطبق: إن شروط الاستخدام هذه وأياً من الحقوق أو الواجبات غير التعاقدية ذات الصلة يجب إخضاعها وتفسيرها للقوانين المطبقة في الدول التي نعمل بها.

        2-      حل النزاعات: في حال وجود مشاكل خاصة بخدماتنا يرجى الاتصال بنا‎. وسنعمل على حل جميع المشاكل التي تواجهك في أقرب وقت ممكن. مع التنويه والتأكيد الى انه سيتم تسوية اي نزاع او خلاف متعلق بشروط الاستخدام هذه عن طريق محاكم الدول التي نعمل بها.

        3-      حقوق الغير: الشخص الذي لا يُعد جزءاً من شروط الاستخدام هذه ليس لديه أي حق في تنفيذ أي من شروطها.

        4-      العلاقة بين الأطراف:  أن كل الأطراف في الاتفاق هي أطراف مستقلة .

        5-      التنازل عن الحقوق والواجبات:  تضمن شروط الاستخدام هذه عدم التنازل او نقل اي من شروط الاستخدام او الحقوق او الواجبات الخاصة بك سواء بشكل مباشر او غير مباشر دون الحصول على موافقة خطية من قبلنا كشرط مسبق.

        6-      الاتفاق:  إن شروط الاستخدام هذه والوثائق المشار إليها أو المدرجة في شروط الاستخدام تمثل مجمل الاتفاق بين الأطراف فيما يتعلق بموضوع الاتفاقية.

        7-      تعديل شروط الاستخدام:  ليس من حق أحد غيرنا إجراء أي تعديل على شروط الاستخدام ( اضافة، حذف، تعديل ) . نحن نحتفظ بالحق في إدخال أي تعديل بما يخص شروط الاستخدام هذه في أي وقت . وسنقوم بنشر النسخة النهائية لشروط الاستخدام على الموقع وستكون سارية المفعول عند نشرها على الموقع أو حسب تاريخ السريان الذي نحدده . إن استخدامك المستمر للخدمات في حال حدوث أي تغييرات يعد موافقة منك على الالتزام بشروط الاستخدام المعدلة.

        8-      الغاء احد الشروط:  في حال تم الغاء اي شرط من شروط الاستخدام هذه من قبل المحاكم المختصة او اعتبر انه غير قانوني فيتم الغاء هذا الشرط وتستمر باقي الشروط والاحكام سارية المفعول بشرط عدم تاثيرها على الصفقات التي تمت تحت مظلتها.

        9-      الظروف والقوة القاهرة:  لا يتحمل أي طرف مسؤولية عن التعطل أو الخسارة أو الضرر أو التأخير أو عدم الوفاء نتيجة للظروف القاهرة الخارجة عن السيطرة لأي من الأطراف  وكمثال لا للحصر ( القضاء والقدر والإجراءات الصادرة عن السلطات التشريعية أو القضائية أو التنظيمية لأي من الحكومة الفيدرالية أو المحلية أو السلطات القضائية أو أي طرف ثالث مورد البضائع أو الخدمات لنا أو الاضطرابات العمالية أو الانقطاع الكامل للتيار الكهربائي أو المقاطعة الاقتصادية).

        10-   التنازل عن شروط الاستخدام:  إن التنازل عن شرط من شروط الاستخدام لا يعني بالمطلق تنازلا عن باقي الشروط المشابهة او غير المشابهة ولا يعتبر مستمرا اذا لم نذكر ذلك خطيا وبشكل صريح.

        11-   التواصل:  يمكنك التواصل معنا عن طريق اتصل بنا او عن طريق ارقام الهاتف او البريد الالكتروني.

        12-   سريان الاحكام والشروط:  ان جميع الأحكام التي ينص على أنها سارية أو التي تسري بطبيعتها بعد إنهاء التعاقد تبقى سارية المفعول بعد إنهاء أو تعليق عضويتك في الموقع.
        TEXT;

        $returnPolicyEn = <<<'TEXT'
        Return Policy

        Scope of Application of this Policy:

        This return policy applies to customers who purchase products from a brand or seller listed on the Deema Fashion website www.deemafashion.com or the Deema Fashion mobile application.

        Returnable Products:

        The following conditions must be met for products to be considered returnable:

        - Deema Fashion reserves the right to reject any return request from the customer if the product does not meet the return requirements (rejected product).
        - If the return request is rejected, the customer is not entitled to a refund.
        - If the product is deemed rejected at any stage after the return, Deema Fashion will make one attempt to return the rejected product to the customer. If the customer does not receive the product, Deema Fashion will retain the product for two business days at the delivery warehouse.
        - The customer has the right to submit a request to return the rejected product through the customer service center within 5 business days of the last notification of the failed delivery.
        - If the customer does not receive the returned products after the last delivery attempt, we will send two reminder emails and make two phone calls to inform them of the return request.
        - If the customer responds to our contact and return attempts and requests a return within 5 business days from the date of the first call, we will deliver the product to them. If they do not respond, the product will be disposed of, and Deema Fashion will not be responsible for the product price or return.

        General Conditions for the Return Process:

        - If the returned product does not meet the company's return conditions, Deema Fashion reserves the right to return the product to the customer.
        - If the product does not meet the return conditions, the customer is not entitled to a refund.
        - If a return request is rejected at any stage, Deema Fashion will make one attempt to return the product to the customer. In the event of a failed delivery attempt, the product will be retained for two business days after notifying the customer of the last failed delivery attempt. The customer may submit a return request to the customer service center within two days of the last notification.
        - If a return request is not received from the customer, the product will be disposed of, and the customer will not be able to receive a refund or submit a new return request.

        Returnable Products and Return Conditions:

        1- Clothing, Shoes, Bags, and Fashion Accessories:
        - Clothing may be returned provided that all tags and labels are intact and in the original packaging, and that the clothing has not been used, worn, washed, or damaged in any way.
        - Underwear, swimwear, bras, socks, and tights are not returnable.
        - Shoes, bags, luggage, eyeglasses, and watches may be returned provided that they are in the original packaging and that all tags and labels are intact and in good condition.

        2- Electronics, Electrical Appliances, and Mobile Phones:
        - If the box has been opened, the tags have been removed, the device has been used, or there is damage, the return request will be rejected. This excludes cases where a manufacturing defect is proven in the product.
        - In case of a manufacturing defect, the product must be returned with the box, manuals, labels, and all accessories in good condition.
        - The original box, with all labels intact and unopened, is a prerequisite for a return.
        - After receiving the product, Deema Fashion will determine whether to repair the product, refund the customer, or return the product.

        3- Beauty and Health:
        - If opened or used, products (skin care, perfumes, cosmetics, etc.) cannot be returned.
        - The following products cannot be returned: vitamins, nutritional supplements, personal care products, and hair care products.
        - If there is a manufacturing or packaging defect in the product, the product must be returned with the original box and all labels and accessories, if any.

        4- Sports and Outdoor Products:
        - Returns are accepted if the product is unused or unopened, and the original labels, tags, and box are present.
        - If a manufacturing defect is found, returns are accepted provided that the original box, labels, and tags are present and the product has not been used.

        5- Home, Kitchen, and Accessories:
        - All bedding and upholstery products cannot be returned if opened.
        - Large household appliances and furniture cannot be returned.
        - Products that can be assembled or installed, or that have undergone modifications, cannot be returned if opened and used, unless a manufacturing defect is found.
        - Home or kitchen decor products are eligible for return if they are unopened, in their original packaging, and with tags, labels, and all accessories intact.

        6- Baby Products:
        - Products are eligible for return if they are unopened, in their original packaging, and with tags, labels, and all accessories intact.
        - The following products are not eligible for return: diapers, accessories, hygiene and bathing products, feeding products, and educational and training products.

        7- Home Maintenance Products:
        - If the box has been opened, the labels have been removed, the device or tools have been used, or there is damage, the return request will be rejected. This excludes cases where a manufacturing defect is proven in the product.
        - In case of a manufacturing defect, the product must be returned with the original packaging, manuals, labels, and all accessories in undamaged condition.

        8- Toys:
        - All costumes and party wear are non-returnable.
        - Games that are used, assembled, opened, or missing parts are not refundable unless there is a manufacturing defect. In this case, the product must be returned with the original box, manuals, labels, and all accessories in good condition.

        9- Stationery, Books, Tools, Office Supplies, and Accessories:
        - If the box has been opened, labels have been removed, the products have been used, or are damaged, the return request will be rejected. This excludes cases where a manufacturing defect is proven in the product.
        - In case of a manufacturing defect, the product must be returned with the original box, booklets, labels, and all accessories in good condition.

        10- Automotive Products and Accessories:
        - If a manufacturing defect is found, the product may be returned provided that the original box, labels, cards, booklets, and all accessories are intact and undamaged.
        - The return request will be rejected if the original box has been opened, the protective cover or original packaging is removed, or any accessories are missing.

        11- Other Products:
        - If the box has been opened, labels have been removed, the products have been used, or are damaged, the return request will be rejected. This excludes cases where a manufacturing defect is proven in the product.
        - In case of a manufacturing defect, the product must be returned with its original packaging, manuals, labels, and all accessories in good condition.

        Procedures Taken Upon Receiving a Return Request from the Customer:
        - Upon receipt of the product in question, we process the request within 3 business days.
        - After processing and accepting the return request, the amount will be refunded within 5 to 7 business days for cash payments. The refunded amount depends on the condition of the returned product.
        - When a product is returned, the refund amount and payment method may vary depending on the condition of the product, the length of time the customer has had the product, and the availability of the return conditions for each product.
        - If you do not receive a response after the processing period has expired, please contact us for further assistance and information.
        TEXT;

        $returnPolicyAr = <<<'TEXT'
        سياسة الإرجاع

        نطاق تطبيق هذه السياسة:

        تطبق سياسة الإرجاع هذه على العملاء الذين يشترون منتجات من علامة تجارية أو بائع مدرج على موقع ديما فاشن www.deemafashion.com أو تطبيق ديما فاشن للجوال.

        المنتجات القابلة للإرجاع:

        يجب استيفاء الشروط التالية حتى تعتبر المنتجات قابلة للإرجاع:

        - تحتفظ ديما فاشن بالحق في رفض أي طلب إرجاع من العميل إذا كان المنتج لا يفي بشروط الإرجاع (منتج مرفوض).
        - إذا تم رفض طلب الإرجاع، فلن يكون العميل مستحقا لأي استرداد.
        - إذا اعتبر المنتج مرفوضا في أي مرحلة بعد الإرجاع، ستقوم ديما فاشن بمحاولة واحدة لإعادة المنتج المرفوض إلى العميل. وإذا لم يستلم العميل المنتج، فسيتم الاحتفاظ به لمدة يومي عمل في مستودع التوصيل.
        - يحق للعميل تقديم طلب لاستلام المنتج المرفوض من خلال مركز خدمة العملاء خلال 5 أيام عمل من آخر إشعار بمحاولة التسليم الفاشلة.
        - إذا لم يستلم العميل المنتجات المعادة بعد آخر محاولة تسليم، فسنرسل رسالتي تذكير عبر البريد الإلكتروني وسنجري اتصالين هاتفيين لإبلاغه بطلب الإرجاع.
        - إذا استجاب العميل لمحاولات التواصل وطلب الاستلام خلال 5 أيام عمل من تاريخ أول اتصال، سيتم تسليم المنتج له. وإذا لم يستجب، فسيتم التخلص من المنتج ولن تتحمل ديما فاشن مسؤولية سعر المنتج أو إرجاعه.

        الشروط العامة لعملية الإرجاع:

        - إذا لم يستوف المنتج المرتجع شروط الإرجاع المعتمدة لدى الشركة، تحتفظ ديما فاشن بالحق في إعادة المنتج إلى العميل.
        - إذا لم يستوف المنتج شروط الإرجاع، فلا يحق للعميل استرداد المبلغ.
        - إذا تم رفض طلب الإرجاع في أي مرحلة، ستقوم ديما فاشن بمحاولة واحدة لإعادة المنتج إلى العميل. وفي حال فشل التسليم، سيتم الاحتفاظ بالمنتج لمدة يومي عمل بعد إشعار العميل بآخر محاولة تسليم فاشلة. ويجوز للعميل تقديم طلب استلام عبر مركز خدمة العملاء خلال يومين من آخر إشعار.
        - إذا لم يتم استلام طلب من العميل، سيتم التخلص من المنتج، ولن يتمكن العميل من استرداد المبلغ أو تقديم طلب إرجاع جديد.

        المنتجات القابلة للإرجاع وشروط الإرجاع:

        1- الملابس والأحذية والحقائب وإكسسوارات الأزياء:
        - يمكن إرجاع الملابس بشرط وجود جميع البطاقات والملصقات وبقائها ضمن التغليف الأصلي، وألا تكون قد استعملت أو لُبست أو غُسلت أو تعرضت لأي تلف.
        - لا يمكن إرجاع الملابس الداخلية وملابس السباحة وحمالات الصدر والجوارب والجوارب الطويلة.
        - يمكن إرجاع الأحذية والحقائب والأمتعة والنظارات والساعات بشرط أن تكون في التغليف الأصلي وأن تكون جميع الملصقات والبطاقات سليمة وبحالة جيدة.

        2- الإلكترونيات والأجهزة الكهربائية والهواتف المحمولة:
        - إذا تم فتح الصندوق أو إزالة الملصقات أو استخدام الجهاز أو وجود تلف، فسيتم رفض طلب الإرجاع. ويستثنى من ذلك حالات وجود عيب مصنعي مثبت.
        - في حال وجود عيب مصنعي، يجب إرجاع المنتج مع الصندوق والكتيبات والملصقات وجميع الملحقات بحالة جيدة.
        - يعتبر وجود الصندوق الأصلي بجميع ملصقاته سليمة وغير مفتوحة شرطا أساسيا للإرجاع.
        - بعد استلام المنتج، تقرر ديما فاشن ما إذا كان سيتم إصلاح المنتج أو رد المبلغ للعميل أو إعادة المنتج إليه.

        3- منتجات الجمال والصحة:
        - لا يمكن إرجاع المنتجات (العناية بالبشرة، العطور، مستحضرات التجميل وغيرها) إذا تم فتحها أو استخدامها.
        - المنتجات التالية غير قابلة للإرجاع: الفيتامينات، المكملات الغذائية، منتجات العناية الشخصية، ومنتجات العناية بالشعر.
        - إذا وجد عيب مصنعي أو عيب في التغليف، يجب إرجاع المنتج مع العبوة الأصلية وجميع الملصقات والملحقات إن وجدت.

        4- المنتجات الرياضية ومنتجات الهواء الطلق:
        - تقبل الإرجاعات إذا كان المنتج غير مستخدم أو غير مفتوح، مع وجود الملصقات والبطاقات والصندوق الأصلي.
        - تقبل الإرجاعات عند وجود عيب مصنعي بشرط وجود الصندوق الأصلي والملصقات والبطاقات وألا يكون المنتج مستخدما.

        5- المنزل والمطبخ والإكسسوارات:
        - لا يمكن إرجاع جميع منتجات المفروشات وأغطية الأسرّة إذا تم فتحها.
        - لا يمكن إرجاع الأجهزة المنزلية الكبيرة والأثاث.
        - المنتجات التي يمكن تركيبها أو تثبيتها أو التي خضعت لتعديلات لا يمكن إرجاعها إذا فُتحت واستُخدمت، ما لم يثبت وجود عيب مصنعي.
        - منتجات ديكور المنزل أو المطبخ قابلة للإرجاع إذا كانت غير مفتوحة وضمن تغليفها الأصلي ومع وجود البطاقات والملصقات وجميع الملحقات.

        6- منتجات الأطفال:
        - المنتجات قابلة للإرجاع إذا كانت غير مفتوحة وضمن التغليف الأصلي ومع البطاقات والملصقات وجميع الملحقات.
        - المنتجات التالية غير قابلة للإرجاع: الحفاضات، والإكسسوارات، ومنتجات النظافة والاستحمام، ومنتجات التغذية، ومنتجات التعليم والتدريب.

        7- منتجات الصيانة المنزلية:
        - إذا تم فتح الصندوق أو إزالة الملصقات أو استخدام الجهاز أو الأدوات أو وجود تلف، فسيتم رفض طلب الإرجاع. ويستثنى من ذلك حالات وجود عيب مصنعي مثبت.
        - في حال وجود عيب مصنعي، يجب إرجاع المنتج مع التغليف الأصلي والكتيبات والملصقات وجميع الملحقات بحالة غير متضررة.

        8- الألعاب:
        - جميع أزياء التنكر وملابس الحفلات غير قابلة للإرجاع.
        - الألعاب التي تم استخدامها أو تركيبها أو فتحها أو كانت ناقصة الأجزاء غير قابلة للاسترداد إلا في حالة وجود عيب مصنعي. وفي هذه الحالة يجب إرجاع المنتج مع الصندوق الأصلي والكتيبات والملصقات وجميع الملحقات بحالة جيدة.

        9- القرطاسية والكتب والأدوات واللوازم المكتبية والإكسسوارات:
        - إذا تم فتح الصندوق أو إزالة الملصقات أو استخدام المنتجات أو تعرضها للتلف، فسيتم رفض طلب الإرجاع. ويستثنى من ذلك حالات وجود عيب مصنعي مثبت.
        - في حال وجود عيب مصنعي، يجب إرجاع المنتج مع الصندوق الأصلي والكتيبات والملصقات وجميع الملحقات بحالة جيدة.

        10- منتجات وإكسسوارات السيارات:
        - في حال وجود عيب مصنعي، يمكن إرجاع المنتج بشرط أن يكون الصندوق الأصلي والملصقات والبطاقات والكتيبات وجميع الملحقات سليمة وغير متضررة.
        - يتم رفض طلب الإرجاع إذا كان الصندوق الأصلي مفتوحا أو تمت إزالة الغلاف الواقي أو التغليف الأصلي أو فقد أي من الملحقات.

        11- منتجات أخرى:
        - إذا تم فتح الصندوق أو إزالة الملصقات أو استخدام المنتجات أو تعرضها للتلف، فسيتم رفض طلب الإرجاع. ويستثنى من ذلك حالات وجود عيب مصنعي مثبت.
        - في حال وجود عيب مصنعي، يجب إرجاع المنتج مع التغليف الأصلي والكتيبات والملصقات وجميع الملحقات بحالة جيدة.

        الإجراءات المتخذة عند استلام طلب إرجاع من العميل:
        - عند استلام المنتج المعني، تتم معالجة الطلب خلال 3 أيام عمل.
        - بعد معالجة طلب الإرجاع وقبوله، يتم رد المبلغ خلال 5 إلى 7 أيام عمل للمدفوعات النقدية. ويعتمد المبلغ المسترد على حالة المنتج المرتجع.
        - عند إرجاع المنتج، قد يختلف مبلغ الاسترداد وطريقة الدفع بحسب حالة المنتج، والمدة التي احتفظ بها العميل بالمنتج، وتوفر شروط الإرجاع الخاصة بكل منتج.
        - إذا لم تتلق ردا بعد انتهاء مدة المعالجة، يرجى التواصل معنا للحصول على المساعدة والمعلومات الإضافية.
        TEXT;

        $warrantyPolicyEn = <<<'TEXT'
        Warranty Policy

        Product Warranty Policy:
        - Deema Fashion's warranty policy protects you and your product from manufacturing defects after purchase.
        - All electrical and electronic devices are subject to the warranty of the manufacturer or supplier according to the period specified by them, with the exception of accessories that follow the manufacturer's or seller's policy.
        - The original invoice containing the serial number and warranty period must be kept to ensure validity. If devices are delivered with a warranty card, customers must visit the service centers and show the card to follow up on their request.
        - The service centers of the seller that issued the warranty card are obligated to carry out warranty repairs in cases where products are purchased through that seller, and the seller's warranty terms apply in this case.
        - Companies, brands, and sellers other than Deema Fashion are obligated to the warranty services they provide, including the provision of spare parts and repair quality. In the event of a complaint, inquiry, or note, customers must contact the warranty service provider for their product.
        - The customer can claim direct compensation from service providers in the event of delay in resolving the warranty claim in accordance with applicable laws in the country, and Deema Fashion bears no responsibility for such compensation.

        Repairing or replacing the device under warranty does not require extending or renewing the warranty period and remains subject to the manufacturer's warranty terms.

        Instructions for Receiving and Delivering Warranty Items:
        - The customer must keep the original packaging of the product and repackage the product safely and properly. Deema Fashion is not responsible for damage during transport if packaging or wrapping is not intact.
        - The product and all its accessories must be delivered by the customer to the delivery representative.
        - If the customer receives a damaged product or one of its accessories is missing, this must be reported within 24 hours of receiving the product. We are not responsible for any claim after this period.
        - The customer must remove all additional accessories (SIM cards, covers, protectors, electronic pens, accessories) when handing over the device, and remains responsible for them. Deema Fashion bears no responsibility for their loss or damage.
        - The address used when submitting the order is approved for both pickup and delivery. If you wish to change it, please contact Deema Fashion customer service. Changes will be shared with the delivery company. Submitting the device or product to the service center also includes implicit customer consent to use contact information by us and the service provider to meet service requirements.
        - Contact information may not be changed during the warranty claim, and Deema Fashion and service centers have the right to use it while processing the request.
        - If the product cannot be repaired under warranty, the customer is entitled to request product replacement or a refund after deducting the value of use and missing parts. Customers must request this directly from brands, commercial companies, or the seller according to applicable laws.
        - The warranty does not cover damage resulting from misuse, accidents, or any external cause unrelated to manufacturing defects.
        - If the warranty card is rejected or the product is out of warranty, some service centers may charge inspection fees.
        - The inspection period does not include the time spent obtaining customer approval or product information.
        - Any data on the device (personal data, contacts, accounts, passwords, etc.) is the customer's responsibility. Customers are requested to back up their data, remove any lock or password, and disable Find My Device before handing the device to the service center.
        - We emphasize that customers must provide correct information when submitting warranty requests.
        - If the customer refuses to receive the product after processing is complete, it will be kept for 15 days from the completion date. After that, the product will be disposed of without any responsibility or compensation on Deema Fashion, including the product price.

        Pick-up and Delivery Policy (If Available):
        - If Deema Fashion provides pickup and delivery service for warranty requests in any area, this is considered an additional service and may include fees charged to customers as determined by Deema Fashion. Deema Fashion reserves the right to discontinue the service at any time.
        - If this service is provided, the processing period is 30 business days as follows:
        - 7 business days: from receipt of the product from the customer until delivery to the seller or service center.
        - 16 business days: processing, inspection, and maintenance time at the seller or service center.
        - 7 business days: delivery of the product to the customer after inspection and maintenance by the seller or service center.

        Seller or Brand Warranty:
        1. Deema Fashion is not responsible for warranty services provided by the seller or brand in terms of repair quality, parts availability, or repair duration, and customers must contact the warranty service provider directly for complaints or inquiries.
        2. The manufacturer or service provider offers a warranty for the device subject to manufacturer, producer, or seller policies. These policies may be found in the user manual or on the official website of the manufacturer or seller. To obtain warranty service, customers can contact manufacturers, sellers, or their authorized service centers according to approved policies.
        3. Deema Fashion provides its own warranty for some devices to ensure service quality.
        TEXT;

        $warrantyPolicyAr = <<<'TEXT'
        سياسة الضمان

        سياسة ضمان المنتجات:
        - تحميك سياسة الضمان في ديما فاشن وتحمي منتجك من عيوب التصنيع بعد الشراء.
        - تخضع جميع الأجهزة الكهربائية والإلكترونية لضمان الشركة المصنعة أو المورد حسب المدة المحددة من قبلهم، باستثناء الملحقات التي تتبع سياسة الشركة المصنعة أو البائع.
        - يجب الاحتفاظ بالفاتورة الأصلية التي تتضمن الرقم التسلسلي ومدة الضمان لضمان سريانه. وإذا تم تسليم الأجهزة مع بطاقة ضمان، يجب على العملاء زيارة مراكز الخدمة وإبراز البطاقة لمتابعة الطلب.
        - تلتزم مراكز خدمة البائع صاحب بطاقة الضمان بإجراء إصلاحات الضمان للحالات التي يتم فيها شراء المنتجات من خلال ذلك البائع، وتطبق شروط ضمان البائع في هذه الحالة.
        - تلتزم الشركات والعلامات التجارية والبائعون غير ديما فاشن بخدمات الضمان التي يقدمونها، بما في ذلك توفير قطع الغيار وجودة الإصلاح. وفي حال وجود شكوى أو استفسار أو ملاحظة، يجب على العملاء التواصل مع مزود خدمة الضمان الخاص بمنتجهم.
        - يحق للعميل المطالبة بتعويض مباشر من مزودي الخدمة في حال التأخر في معالجة مطالبة الضمان وفقا للقوانين النافذة في الدولة، ولا تتحمل ديما فاشن أي مسؤولية عن هذا التعويض.

        إن إصلاح الجهاز أو استبداله ضمن الضمان لا يستلزم تمديد فترة الضمان أو تجديدها، ويظل خاضعا لشروط الضمان وفق سياسة الشركة المصنعة للجهاز.

        تعليمات استلام وتسليم الضمان:
        - يجب على العميل الاحتفاظ بالتغليف الأصلي للمنتج وإعادة تغليف المنتج بطريقة آمنة وسليمة. ولا تتحمل ديما فاشن مسؤولية أي ضرر أثناء نقل المنتج إذا لم يكن التغليف سليما.
        - يجب على العميل تسليم المنتج وجميع ملحقاته إلى مندوب التوصيل.
        - إذا استلم العميل منتجا متضررا أو كان أحد ملحقاته مفقودا، فيجب الإبلاغ عن ذلك خلال 24 ساعة من استلام المنتج، ولا نتحمل أي مطالبة بعد هذه المدة.
        - يجب على العميل إزالة جميع الملحقات الإضافية (شرائح الاتصال، الأغطية، الواقيات، الأقلام الإلكترونية، الإكسسوارات) عند تسليم الجهاز، ويكون مسؤولا عنها، ولا تتحمل ديما فاشن أي مسؤولية عن فقدانها أو تلفها.
        - يعتمد العنوان المستخدم عند تقديم الطلب لعمليتي الاستلام والتسليم. وإذا رغبت بتعديله، يرجى التواصل مع خدمة عملاء ديما فاشن، وسيتم مشاركة التعديل مع شركة التوصيل. كما أن تسليم الجهاز أو المنتج إلى مركز الخدمة يتضمن موافقة ضمنية من العميل على استخدام معلومات التواصل من قبلنا ومن قبل مزود الخدمة لتلبية متطلبات الخدمة.
        - لا يمكن تغيير معلومات التواصل أثناء مطالبة الضمان، ويحق لديما فاشن ولمراكز الخدمة استخدامها أثناء معالجة الطلب.
        - إذا تعذر إصلاح المنتج ضمن الضمان، يحق للعميل طلب استبدال المنتج أو استرداد المبلغ بعد خصم قيمة الاستخدام وقيمة الأجزاء المفقودة. ويجب على العملاء طلب ذلك مباشرة من العلامات التجارية أو الشركات التجارية أو البائع وفق القوانين النافذة.
        - لا يغطي الضمان الأضرار الناتجة عن سوء الاستخدام أو الحوادث أو أي سبب خارجي لا يتعلق بعيوب التصنيع.
        - في حال رفض بطاقة الضمان أو كان المنتج خارج الضمان، قد تفرض بعض مراكز الخدمة رسوما على الفحص.
        - لا تشمل مدة الفحص الوقت المستغرق للحصول على موافقة العميل أو معلومات المنتج.
        - أي بيانات موجودة على الجهاز (بيانات شخصية، جهات اتصال، حسابات، كلمات مرور، وغيرها) هي مسؤولية العميل. لذلك يرجى عمل نسخة احتياطية وإزالة أي قفل أو كلمة مرور وإيقاف خدمة Find My Device قبل تسليم الجهاز لمركز الخدمة.
        - نؤكد على العملاء عند طلب الضمان ضرورة تقديم معلومات صحيحة.
        - في حال رفض العميل استلام المنتج بعد إتمام المعالجة، سيتم الاحتفاظ به لمدة 15 يوما من تاريخ اكتمال المعالجة، وبعد ذلك سيتم التخلص من المنتج دون أي مسؤولية أو تعويض على ديما فاشن، بما في ذلك قيمة المنتج.

        سياسة الاستلام والتسليم (إن وجدت):
        - إذا وفرت ديما فاشن خدمة الاستلام والتسليم لطلبات الضمان في أي منطقة ممكنة، فتعد هذه خدمة إضافية وقد يترتب عليها رسوم وتكاليف على العملاء تحددها ديما فاشن، وتحتفظ ديما فاشن بحق إيقاف الخدمة متى تشاء.
        - في حال توفير هذه الخدمة، تكون مدة معالجة الطلب 30 يوم عمل موزعة كما يلي:
        - 7 أيام عمل: من تاريخ استلام المنتج من العميل وحتى تسليمه إلى البائع أو مركز الخدمة.
        - 16 يوم عمل: مدة المعالجة والفحص والصيانة لدى البائع أو مركز الخدمة.
        - 7 أيام عمل: تسليم المنتج إلى العميل بعد الفحص والصيانة من قبل البائع أو مركز الخدمة.

        ضمان البائع أو العلامة التجارية:
        1. ديما فاشن غير مسؤولة عن خدمة الضمان المقدمة من البائع أو العلامة التجارية من حيث جودة الإصلاح أو توفر القطع أو مدة الإصلاح، ويجب على العملاء التواصل مباشرة مع مزود خدمة الضمان في حال وجود شكوى أو استفسار.
        2. تقدم الشركة المصنعة أو مزود الخدمة ضمانا للجهاز وفقا لسياسات الشركة المصنعة أو المنتج أو البائع. وقد تتوفر هذه السياسات في دليل المستخدم الخاص بالجهاز أو على الموقع الرسمي للشركة المصنعة أو البائع. وللحصول على خدمة الضمان، يمكن للعملاء التواصل مع المصنعين أو البائعين أو مراكز الخدمة المعتمدة لديهم وفقا لسياساتهم المعتمدة.
        3. توفر ديما فاشن ضمانها الخاص لبعض الأجهزة لضمان جودة الخدمة.
        TEXT;

        $termsOfSaleEn = <<<'TEXT'
        Terms of Sale

        Last updated: 02/15/2025

        Introduction

        The terms of sale in this document are the terms and conditions under which purchases are received and delivered to the customer as a buyer on the website www.deemafashion.com or through our mobile application, owned and operated by Online Tech Mall Limited. Please read and understand these terms carefully before making any purchase through the website or application. If you make a purchase through the website, this constitutes your acknowledgment and agreement to these terms of sale and your commitment to all their provisions. You must also review, read, and understand the privacy policy adopted on our website and application, as your use and all transactions are subject to it.

        Approved Definitions:

        1- Purchase Order: When you place a purchase order, we will notify you via email, SMS, or a notification on the application and website of our acceptance or rejection of the order. In this case, you will not be charged any amount or product value unless the order is confirmed.

        2- Supplier: All products available on our website and mobile application are sold by Deema Fashion or by a local or international seller.

        3- Payment: Upon placing and confirming a purchase order, you authorize Deema Fashion or any third party specialized in electronic payments with whom we have contracts to deduct the purchase value from your card, or to collect payment in cash on delivery.

        4- Payment by Credit Card (if available): We may require you to open an account with our contracted electronic payment companies, and this means accepting their terms and conditions. We also reserve the right to add or remove any approved payment method at any time without prior notice.

        5- Cancellation of Purchase Order: We allow you to cancel your order during the packaging stage for any reason, before shipping and delivery start.

        6- Order Rejection by Us: We reserve the right to reject your order if you fail to pay the purchase value, fail to provide required delivery details, or are unable to receive the order.

        7- Delivery of Orders: Delivery cost and expected date are clearly shown on the website and application.

        8- Late Delivery: Delivery delays may happen for several reasons, including:
        - You are not available at the delivery address and time. In this case, we will inform you about the delivery procedure.
        - You are unable to receive the order for any reason, or unable to schedule another delivery date. In this case, we will contact you for additional delivery details. The order will be canceled if we are unable to reach you.
        - Delays may occur due to factors beyond our control. We strive to reduce the impact of any delay and we will contact you directly in all cases.

        9- Invoices: We provide an electronic invoice for your purchases, sent to your email address. Please make sure you provide a valid and correct email.

        10- Product Ownership: After you place your order, pay for it, and receive it at the delivery address, ownership of the product transfers fully to you.

        Warranty:

        - Deema Fashion warranty is subject to our approved warranty policy. We provide warranty for certain products sold by us, and warranty availability may depend on suppliers and their approved terms and policies. Warranty applies only to manufacturing, material, or design defects. Product warranty is limited to repairing the defective product, replacing the defective part, replacing the product, or refunding the paid amount according to the paid price.
        - Warranty does not apply to all products and depends on supplier warranty availability and policy. Please always review the warranty policy on our website and app, along with supplier policies and warranty cards.
        - If products are purchased from another seller, that seller's warranty terms apply. For details, review the approved warranty policy on our website and app.
        - Repairing or replacing the product does not require extending or renewing the warranty period.
        - Replacement, repair, or refund rules are as follows:
          1. Maximum repair attempts before replacement or refund: 3.
          2. Maximum repair period: 15 business days.
          3. Repair-eligible categories include electrical appliances, electronics, watches, electronics accessories, toys, children's supplies, sports equipment, and electrical tools.
          4. If repair is not possible after the permitted attempts, the product is replaced or the customer is refunded.
        - Warranty is void in these cases:
          1. Removing or hiding the product serial number.
          2. Attempting repair at non-authorized service centers.
          3. Damage or failure to any part that prevents proper operation or use (broken screen, puncture, bending, etc.).
          4. Liquid damage by immersion or spray causing damage to the product or any of its parts.
          5. Tampering with operating software.
          6. Using unauthorized or non-original accessories.
          7. Replacing consumables (ink, batteries, etc.).
          8. Misuse that violates manufacturer usage instructions.
          9. Tampering with labels, seals, and tags.

        Returns:

        1- Return Conditions:
        - Original packaging is unopened.
        - Product is unused.
        - All labels and tags are present and in good condition.
        - Product differs from its description or image shown on the website or app.

        2- Returns: Some products are eligible for return. Please review the returns policy on our website and app.

        3- Reasons for Return and Exchange:
        - Receiving a damaged or defective product.
        - Receiving a product that does not match the listed description.
        - Receiving the wrong product.

        4- Non-Returnable Products:
        - Products that were used, damaged, spoiled, or altered from original condition.
        - Products with damaged, altered, or erased serial numbers.
        - Products classified as hazardous or containing flammable materials.
        - Products mixed or used with other products or materials.
        - Products listed as non-returnable in our return policy.

        5- Refund Process:
        For returnable products we approve for return, the customer receives full paid value including applicable fees, excluding shipping fees if paid, in the following cases:
        - Manufacturing defect.
        - Product does not match description on website/app.
        - Error from our side (pricing error, description error, delivery delay).
        In all other cases, we refund only the value of the returnable product (excluding shipping fees paid for delivery to you), and the customer bears return shipping cost.
        For undelivered products, full refund applies if you cancel according to Clause 5 under Approved Definitions.

        6- Refund Procedures:
        Refunds are made through the same payment method selected during checkout within a maximum of 15 days from receiving the returned product, or immediately when cancellation occurs during packaging stage.

        Customer Legal Obligations:
        Once you register an account with Deema Fashion, you agree to:
        1. Comply with all applicable laws and regulations in countries where we operate, including privacy laws.
        2. Confirm you have full legal capacity to agree and pay all due amounts.
        3. Accept services are provided on an "as-is" basis without special warranties.
        4. Accept that we disclaim direct and indirect warranties such as merchantability, non-infringement, secure or error-free services.
        5. Acknowledge that the only warranty provided is the one in the Warranty section of this document.

        General Provisions:
        1- These terms do not exempt either party from liability for fraud or deception, death or personal injury caused by negligence, or any liability that cannot be excluded by law.
        2- We are not liable for damage, harm, data or profit loss, even if notified, in the following cases:
        - Use of, or inability to use, the product.
        - Late delivery or failure to deliver due to your failure or delay in providing requested information.
        - Damage resulting from non-authorized repair.
        - Loss of stored data in repaired or replaced products.
        - Interruption or delay of website, app, or services, and resulting effects.
        - Damage to your device resulting from purchased products.
        - Viruses or malicious software resulting from product use.
        - Reliance only on product information shown on our website when ordering.
        - Any emergency or circumstance beyond our control.
        3- Our total legal liability for breach of duties related to these terms is limited to compensation equal to product price and shipping, delivery, and return costs only. You agree to this limitation and release us from other losses, damages, and expenses arising from:
        - Your non-compliance with any terms of this document.
        - Third-party claims resulting from your use of the website, app, or services.
        - Violations of applicable laws and regulations.
        4- These terms and related obligations are governed by the laws in countries where we operate.
        5- No party other than parties to this agreement may enforce its provisions.
        6- We may amend the Terms of Sale at any time and notify you by posting updates on our website and app. Updates are effective from the posting date. Any purchase after posting constitutes acknowledgment and acceptance.
        7- Neither party is liable for loss, delay, or service interruption caused by force majeure or circumstances beyond control, including acts of God, court rulings, new laws, regulatory decisions, international sanctions, economic boycotts, power outages, or similar events affecting service continuity.
        8- These Terms of Sale remain effective even after suspension or cancellation of your membership on the website and app.
        TEXT;

        $termsOfSaleAr = <<<'TEXT'
        شروط البيع

        آخر تحديث: 15/02/2025

        المقدمة:

        شروط البيع الواردة في هذه الوثيقة هي الأحكام والشروط التي يتم بموجبها استلام المشتريات وتسليمها للعميل بصفته مشتريا عبر موقع www.deemafashion.com أو عبر تطبيقنا للجوال المملوك والمدار من قبل شركة Online Tech Mall Limited. لذلك يرجى قراءة هذه الشروط وفهمها جيدا قبل إجراء أي عملية شراء عبر الموقع أو التطبيق. إن قيامك بالشراء عبر الموقع يعد إقرارا منك وموافقة على شروط البيع والتزاما بجميع أحكامها. كما يجب عليك مراجعة وقراءة وفهم سياسة الخصوصية المعتمدة على موقعنا وتطبيقنا، حيث يخضع استخدامك وجميع معاملاتك لها.

        التعريفات المعتمدة:

        1- طلب الشراء: عند تقديم طلب شراء، سنقوم بإشعار العميل عبر البريد الإلكتروني أو رسالة هاتفية أو إشعار داخل التطبيق والموقع بقبول الطلب أو رفضه. وفي هذه الحالة لا يتم تحصيل أي مبلغ أو قيمة المنتج إلا بعد تأكيد الطلب.

        2- المورد: جميع المنتجات المعروضة على موقعنا وتطبيقنا تباع من قبل ديما فاشن أو من قبل بائع محلي أو دولي.

        3- الدفع: عند تقديم وتأكيد طلب الشراء، فإنك تفوض ديما فاشن أو أي طرف ثالث متخصص في الدفع الإلكتروني ومتعاقد معنا بخصم قيمة الشراء من بطاقتك أو تحصيلها نقدا عند الاستلام.

        4- الدفع بالبطاقة (إن توفر): قد نطلب منك فتح حساب لدى شركات الدفع الإلكتروني المتعاقد معها، ويعني ذلك قبول شروطها وأحكامها. كما نحتفظ بحق إضافة أو حذف أي وسيلة دفع معتمدة في أي وقت ودون إشعار مسبق.

        5- إلغاء طلب الشراء: نتيح لك إلغاء الطلب خلال مرحلة التغليف لأي سبب وقبل بدء الشحن والتسليم.

        6- رفض الطلب من طرفنا: نحتفظ بحق رفض الطلب إذا لم يتم سداد قيمة الشراء، أو لم يتم تزويدنا بمعلومات التسليم المطلوبة، أو تعذر عليك استلام الطلب.

        7- تسليم الطلبات: يتم توضيح تكلفة وموعد التسليم بشكل واضح على الموقع والتطبيق.

        8- التأخر في التسليم: قد يحدث التأخر لعدة أسباب، من أهمها:
        - عدم تواجدك في عنوان ووقت التسليم، وفي هذه الحالة سنبلغك بآلية التسليم.
        - عدم القدرة على استلام الطلب لأي سبب أو عدم القدرة على تحديد موعد تسليم آخر. في هذه الحالة سنتواصل معك لمعلومات إضافية، ويُلغى الطلب إذا تعذر التواصل.
        - قد تحدث التأخيرات لأسباب خارجة عن إرادتنا، ونبذل جهدنا لتقليل آثار التأخير، وسنتواصل معك مباشرة في جميع الحالات.

        9- الفواتير: نوفر لك فاتورة إلكترونية بقيمة مشترياتك ترسل إلى بريدك الإلكتروني، ويرجى التأكد من تزويدنا ببريد صحيح وسليم.

        10- ملكية المنتج: بعد تقديم الطلب وسداد قيمته واستلامه على عنوان التسليم تصبح ملكية المنتج لك بالكامل.

        الضمان:

        - يخضع ضمان ديما فاشن لسياسة الضمان المعتمدة لدينا، حيث نوفر ضمانا لبعض المنتجات المباعة من طرفنا. وقد يتوقف الضمان على الموردين أصحاب المنتجات المعروضة وفقا لسياساتهم وشروطهم المعتمدة. ولا يطبق الضمان إلا على عيوب التصنيع أو المواد أو التصميم. ويقتصر الضمان على إصلاح المنتج المعيب أو استبدال الجزء المعيب أو استبدال المنتج أو رد المبلغ المدفوع وفق السعر المدفوع.
        - لا يشمل الضمان جميع المنتجات، إذ يعتمد على توفر الضمان من الموردين حسب سياساتهم. لذلك يرجى مراجعة سياسة الضمان على موقعنا وتطبيقنا، إضافة إلى سياسات الموردين وبطاقات الضمان الخاصة بهم.
        - في حال شراء المنتجات من بائع آخر، تطبق شروط ضمان ذلك البائع. ولمزيد من المعلومات يرجى الرجوع إلى سياسة الضمان المعتمدة على موقعنا وتطبيقنا.
        - إصلاح المنتج أو استبداله لا يترتب عليه تمديد أو تجديد مدة الضمان.
        - أحكام الاستبدال أو الإصلاح أو رد المبلغ:
          1. عدد محاولات الإصلاح قبل الاستبدال أو الاسترداد هو 3 محاولات.
          2. الحد الأقصى لمدة الإصلاح 15 يوم عمل.
          3. المنتجات القابلة للإصلاح تشمل: الأجهزة الكهربائية، الإلكترونيات، الساعات، ملحقات الإلكترونيات، الألعاب، مستلزمات الأطفال، المعدات الرياضية، والأدوات الكهربائية.
          4. إذا تعذر الإصلاح بعد المحاولات المسموح بها، يتم استبدال المنتج أو رد المبلغ للعميل.
        - يبطل الضمان في الحالات التالية:
          1. إزالة الرقم التسلسلي للمنتج أو إخفاؤه.
          2. محاولة إصلاح المنتج لدى مراكز غير معتمدة من قبلنا أو من قبل البائع.
          3. حدوث ضرر أو عطل في أي جزء يمنع تشغيل المنتج أو استخدامه (كسر شاشة، ثقب، التواء، وغيرها).
          4. دخول السوائل للمنتج بالغمر أو الرش بما يؤدي إلى تلف المنتج أو أحد أجزائه.
          5. العبث ببرمجيات تشغيل الجهاز.
          6. استخدام ملحقات خارجية غير أصلية أو غير معتمدة.
          7. استبدال المواد الاستهلاكية للمنتج (الأحبار، البطاريات وغيرها).
          8. سوء استخدام المنتج بما يخالف تعليمات التشغيل والاستعمال المعتمدة من الشركة المصنعة.
          9. العبث بملصقات المنتج والأختام والبطاقات.

        الإرجاع:

        1- شروط الإرجاع:
        - أن يكون التغليف الأصلي غير مفتوح.
        - أن يكون المنتج غير مستخدم.
        - أن تكون جميع الملصقات والبطاقات موجودة وبحالة جيدة.
        - أن يكون المنتج مختلفا عن الوصف أو الصورة المعروضة في بطاقة المنتج على الموقع أو التطبيق.

        2- الإرجاع: بعض المنتجات تقبل الإرجاع. يمكنك الاطلاع على سياسة الإرجاع عبر موقعنا وتطبيقنا.

        3- أسباب الإرجاع والاستبدال:
        - استلام منتج تالف أو معيب.
        - استلام منتج غير مطابق للوصف.
        - استلام منتج خاطئ.

        4- المنتجات غير القابلة للإرجاع:
        - المنتجات التي تم استخدامها أو إتلافها أو تلفها أو تغيير حالتها الأصلية.
        - المنتجات التي تعرض الرقم التسلسلي فيها للتلف أو التعديل أو الطمس.
        - المنتجات المصنفة كمواد خطرة أو القابلة للاشتعال.
        - المنتجات التي تم خلطها أو استخدامها مع مواد أو منتجات أخرى.
        - المنتجات المدرجة ضمن سياسة الإرجاع كغير قابلة للإرجاع.

        5- آلية الاسترداد:
        بالنسبة للمنتجات القابلة للإرجاع والتي نوافق على إرجاعها، يتم رد كامل المبلغ المدفوع بما فيه الرسوم المطبقة باستثناء رسوم الشحن إن وجدت في الحالات التالية:
        - عيب تصنيعي في المنتج.
        - عدم مطابقة المنتج للوصف على الموقع والتطبيق.
        - خطأ من طرفنا مثل خطأ تسعير أو خطأ وصف أو تأخر في التسليم.
        وفي جميع الحالات الأخرى يتم رد قيمة المنتج القابل للإرجاع فقط (دون رسوم الشحن المدفوعة لتوصيل المنتج إليك)، ويتحمل العميل تكلفة إعادة المنتج.
        أما المنتجات التي لم يتم تسليمها، فيتم رد كامل المبلغ عند إلغاء الطلب وفقا للبند 5 من قسم التعريفات المعتمدة.

        6- إجراءات الاسترداد:
        يتم رد المبلغ المدفوع عبر نفس وسيلة الدفع المستخدمة عند الطلب خلال مدة أقصاها 15 يوما من استلام المنتج المرتجع، أو فورا عند إلغاء الطلب خلال مرحلة التغليف.

        الالتزامات القانونية على العميل:
        بمجرد تسجيل حساب لدى ديما فاشن، يوافق العميل على ما يلي:
        1. الالتزام بجميع القوانين والأنظمة النافذة في الدول التي نعمل بها، بما في ذلك قوانين حماية الخصوصية.
        2. أن العميل يتمتع بالأهلية القانونية الكاملة للموافقة وسداد جميع المستحقات.
        3. أن خدماتنا تقدم كما هي دون أي ضمانات خاصة.
        4. إخلاء مسؤوليتنا من أي ضمانات أو شروط مباشرة أو غير مباشرة مثل القابلية التجارية أو عدم الانتهاك أو أمن الخدمات أو خلوها من الأخطاء.
        5. أن الضمان الوحيد المقدم من طرفنا هو الوارد في قسم الضمان من هذه الوثيقة.

        أحكام عامة:
        1- لا تعفي شروط البيع المذكورة أي طرف من المسؤولية عن الاحتيال أو التدليس، أو عن الوفاة أو الإصابة الشخصية الناتجة عن الإهمال، أو أي مسؤولية لا يجوز الإعفاء منها قانونا.
        2- لا نتحمل أي ضرر أو خسارة أو فقد معلومات أو أرباح أو بيانات حتى لو تم إخطارنا بذلك، في الحالات التالية:
        - استخدام المنتج أو عدم القدرة على استخدامه.
        - التأخر في التسليم أو عدم تسليم الطلب أو جزء منه بسبب عدم تقديمك للمعلومات المطلوبة أو تأخرك في ذلك.
        - أي ضرر ناتج عن إصلاح غير معتمد من طرفنا.
        - فقدان البيانات المخزنة في المنتجات التي تم إصلاحها أو استبدالها.
        - تعطل أو تأخر الموقع أو التطبيق أو الخدمات وما يترتب على ذلك.
        - الأضرار التي تصيب جهازك نتيجة استخدام المنتجات التي اشتريتها.
        - الفيروسات والبرمجيات الخبيثة الناتجة عن استخدام المنتج.
        - اعتمادك فقط على معلومات المنتج المتوفرة على الموقع عند الطلب.
        - أي حدث أو ظرف طارئ خارج عن إرادتنا.
        3- تقتصر مسؤوليتنا القانونية عند الإخلال أو مخالفة الواجبات القانونية المرتبطة بشروط البيع على تعويض يساوي سعر المنتج المباع وتكاليف الشحن والتسليم والإرجاع فقط. وتوافق على هذا الحد من المسؤولية وإبرائنا من أي خسائر أو أضرار أو نفقات أخرى ناتجة عن:
        - عدم التزامك بأي من شروط هذه الوثيقة.
        - مطالبات أو دعاوى طرف ثالث بسبب استخدامك للموقع أو التطبيق أو أي من خدماتنا.
        - مخالفة القوانين والأنظمة والتشريعات المعمول بها.
        4- تخضع شروط البيع والالتزامات الناشئة عنها للقوانين والتشريعات النافذة في الدول التي نعمل بها.
        5- لا يحق لأي طرف غير أطراف هذه الاتفاقية تنفيذ أحكامها.
        6- نحتفظ بحق تعديل أو تغيير شروط البيع في أي وقت، ويتم إشعارك بذلك من خلال نشر التعديلات على الموقع والتطبيق. وتصبح التحديثات نافذة من تاريخ نشرها. ويعد استمرارك بالشراء بعد النشر موافقة منك على التعديلات والالتزام بها.
        7- لا يتحمل أي طرف مسؤولية أي خسارة أو ضرر أو تأخير أو انقطاع للخدمات بسبب ظروف أو أحداث خارجة عن السيطرة أو غير متوقعة أو طارئة، بما في ذلك الكوارث الطبيعية، الأحكام القضائية، القوانين الجديدة، القرارات التنظيمية، العقوبات الدولية، المقاطعات الاقتصادية، انقطاع الكهرباء، أو أي أحداث تؤثر على استمرارية الخدمة.
        8- تبقى شروط وأحكام البيع هذه سارية حتى بعد تعليق أو إلغاء عضويتك في الموقع والتطبيق.
        TEXT;

        $driverPrivacyEn = <<<'TEXT'
        Driver Privacy Policy

        This policy explains how Deema Fashion handles driver data needed to operate the delivery service, including location tracking during active orders, contact details, and performance metrics. Data is used to assign orders, provide support, improve service quality, and comply with legal requirements.
        TEXT;

        $driverPrivacyAr = <<<'TEXT'
        سياسة خصوصية المندوب

        توضح هذه السياسة كيفية تعامل ديما فاشن مع بيانات المندوب اللازمة لتشغيل خدمة التوصيل، بما في ذلك تتبع الموقع أثناء الطلبات النشطة، وبيانات التواصل، ومؤشرات الأداء. تُستخدم البيانات لتوزيع الطلبات، وتقديم الدعم، وتحسين جودة الخدمة، والامتثال للمتطلبات القانونية.
        TEXT;

        $driverTermsEn = <<<'TEXT'
        Driver Terms & Conditions

        By using the driver app, you agree to comply with delivery procedures, communicate respectfully with customers and support, and follow all safety requirements. Violations may result in suspension or account termination.
        TEXT;

        $driverTermsAr = <<<'TEXT'
        شروط وأحكام المندوب

        باستخدامك لتطبيق المندوب، فإنك توافق على الالتزام بإجراءات التوصيل، والتواصل باحترام مع العملاء والدعم، واتباع متطلبات السلامة. المخالفات قد تؤدي إلى إيقاف الحساب أو إنهائه.
        TEXT;

        LegalDocument::updateOrCreate(
            ['key' => 'privacy_policy'],
            [
                'title' => [
                    'en' => 'Privacy Policy',
                    'ar' => 'سياسة الخصوصية',
                ],
                'content' => [
                    'en' => $englishText,
                    'ar' => $arabicText,
                ],
            ]
        );

        LegalDocument::updateOrCreate(
            ['key' => 'terms_conditions'],
            [
                'title' => [
                    'en' => 'Terms of Use',
                    'ar' => 'شروط الاستخدام',
                ],
                'content' => [
                    'en' => $termsOfUseEn,
                    'ar' => $termsOfUseAr,
                ],
            ]
        );

        LegalDocument::updateOrCreate(
            ['key' => 'return_policy'],
            [
                'title' => [
                    'en' => 'Return Policy',
                    'ar' => 'سياسة الإرجاع',
                ],
                'content' => [
                    'en' => $returnPolicyEn,
                    'ar' => $returnPolicyAr,
                ],
            ]
        );

        LegalDocument::updateOrCreate(
            ['key' => 'warranty_policy'],
            [
                'title' => [
                    'en' => 'Warranty Policy',
                    'ar' => 'سياسة الضمان',
                ],
                'content' => [
                    'en' => $warrantyPolicyEn,
                    'ar' => $warrantyPolicyAr,
                ],
            ]
        );

        LegalDocument::updateOrCreate(
            ['key' => 'termsOfSale'],
            [
                'title' => [
                    'en' => 'Terms of Sale',
                    'ar' => 'شروط البيع',
                ],
                'content' => [
                    'en' => $termsOfSaleEn,
                    'ar' => $termsOfSaleAr,
                ],
            ]
        );

        LegalDocument::updateOrCreate(
            ['key' => 'marketer_terms_conditions'],
            [
                'title' => [
                    'en' => 'Marketer Terms & Conditions',
                    'ar' => 'شروط وأحكام المسوقين',
                ],
                'content' => [
                    'en' => $marketerTermsEn,
                    'ar' => $marketerTermsAr,
                ],
            ]
        );

        LegalDocument::updateOrCreate(
            ['key' => 'privacy_policy_driver'],
            [
                'title' => [
                    'en' => 'Driver Privacy Policy',
                    'ar' => 'سياسة خصوصية المندوب',
                ],
                'content' => [
                    'en' => $driverPrivacyEn,
                    'ar' => $driverPrivacyAr,
                ],
            ]
        );

        LegalDocument::updateOrCreate(
            ['key' => 'terms_conditions_driver'],
            [
                'title' => [
                    'en' => 'Driver Terms & Conditions',
                    'ar' => 'شروط وأحكام المندوب',
                ],
                'content' => [
                    'en' => $driverTermsEn,
                    'ar' => $driverTermsAr,
                ],
            ]
        );
    }
}



INSERT INTO site_feature_information (site_id, extension_id, feature_id, is_on) SELECT id, 'org.openacalendar', 'Group', '1' FROM site_information WHERE is_feature_group = '1';
INSERT INTO site_feature_information (site_id, extension_id, feature_id, is_on) SELECT id, 'org.openacalendar', 'PhysicalEvents', '1' FROM site_information WHERE is_feature_physical_events = '1';
INSERT INTO site_feature_information (site_id, extension_id, feature_id, is_on) SELECT id, 'org.openacalendar', 'VirtualEvents', '1' FROM site_information WHERE is_feature_virtual_events = '1';
INSERT INTO site_feature_information (site_id, extension_id, feature_id, is_on) SELECT id, 'org.openacalendar.curatedlists', 'CuratedList', '1' FROM site_information WHERE is_feature_curated_list = '1';
INSERT INTO site_feature_information (site_id, extension_id, feature_id, is_on) SELECT id, 'org.openacalendar', 'Importer', '1' FROM site_information WHERE is_feature_importer = '1';
INSERT INTO site_feature_information (site_id, extension_id, feature_id, is_on) SELECT id, 'org.openacalendar', 'Map', '1' FROM site_information WHERE is_feature_map = '1';
INSERT INTO site_feature_information (site_id, extension_id, feature_id, is_on) SELECT id, 'org.openacalendar', 'Tag', '1' FROM site_information WHERE is_feature_tag = '1';


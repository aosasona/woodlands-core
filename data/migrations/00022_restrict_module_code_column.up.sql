-- We had to relax the constraints when we added the module code column, but now we can make it more strict.
ALTER TABLE `modules` MODIFY COLUMN `code` VARCHAR(16) NOT NULL;
-- split
ALTER TABLE `modules` ADD UNIQUE INDEX `uk_module_code` (`code`);

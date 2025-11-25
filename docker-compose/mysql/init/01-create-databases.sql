-- Create all required databases for Heroes Profile
CREATE DATABASE IF NOT EXISTS heroesprofile;
CREATE DATABASE IF NOT EXISTS heroesprofile_globals;
CREATE DATABASE IF NOT EXISTS heroesprofile_cache;
CREATE DATABASE IF NOT EXISTS heroesprofile_logs;
CREATE DATABASE IF NOT EXISTS heroesprofile_ngs;
CREATE DATABASE IF NOT EXISTS heroesprofile_ccl;
CREATE DATABASE IF NOT EXISTS heroesprofile_mcl;
CREATE DATABASE IF NOT EXISTS heroesprofile_hi;
CREATE DATABASE IF NOT EXISTS heroesprofile_hi_nc;

-- Grant permissions to the heroesprofile user for all databases
GRANT ALL PRIVILEGES ON heroesprofile.* TO 'heroesprofile'@'%';
GRANT ALL PRIVILEGES ON heroesprofile_globals.* TO 'heroesprofile'@'%';
GRANT ALL PRIVILEGES ON heroesprofile_cache.* TO 'heroesprofile'@'%';
GRANT ALL PRIVILEGES ON heroesprofile_logs.* TO 'heroesprofile'@'%';
GRANT ALL PRIVILEGES ON heroesprofile_ngs.* TO 'heroesprofile'@'%';
GRANT ALL PRIVILEGES ON heroesprofile_ccl.* TO 'heroesprofile'@'%';
GRANT ALL PRIVILEGES ON heroesprofile_mcl.* TO 'heroesprofile'@'%';
GRANT ALL PRIVILEGES ON heroesprofile_hi.* TO 'heroesprofile'@'%';
GRANT ALL PRIVILEGES ON heroesprofile_hi_nc.* TO 'heroesprofile'@'%';

FLUSH PRIVILEGES;
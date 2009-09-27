try:
    from setuptools import setup, find_packages
except ImportError:
    from ez_setup import use_setuptools
    use_setuptools()
    from setuptools import setup, find_packages

setup(
	name='memorised',
	version='1.0',
	author='Wes Mason',
	author_email='wes [at] 1stvamp [dot] org',
	url='http://github.com/1stvamp/oneversion/tree/master/python/memorised/',
	packages=['memorised'],
	install_requires=['python-memcached'],
)
